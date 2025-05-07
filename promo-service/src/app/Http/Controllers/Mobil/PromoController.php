<?php

namespace App\Http\Controllers\Mobil;

use App\Jobs\PrizePromoUpdateJob;
use DB;
use App\Models\Prize;
use App\Models\Platform;
use App\Models\PrizePromo;
use App\Models\PromoAction;
use Illuminate\Http\Request;
use App\Models\PromoCodeUser;
use Illuminate\Support\Carbon;
use App\Jobs\PromoCodeConsumeJob;
use App\Jobs\CreatePromoActionJob;
use App\Http\Controllers\Controller;
use App\Services\ViaPromocodeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Http\Resources\PromotionResource;
use App\Repositories\PromoCodeRepository;
use App\Repositories\PromotionRepository;
use App\Http\Requests\SendPromocodeRequest;
use App\Models\SmartRandomRule;
use Illuminate\Support\Facades\Log;

class PromoController extends Controller
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private PromotionRepository $promotionRepository,
        private PromoCodeRepository $promoCodeRepository
    ) {
        $this->viaPromocodeService = $viaPromocodeService;
        $this->promotionRepository = $promotionRepository;
        $this->promoCodeRepository = $promoCodeRepository;
    }
    public function index()
    {

        return $this->successResponse(['promotions' => PromotionResource::collection($this->viaPromocodeService->getPromotion())], "success");
    }
    public function viaPromocode(SendPromocodeRequest $request, $id)
    {
        $user = $request['auth_user'];
        $req = $request->validated();
        $promocodeInput = $req['promocode'];

        $action = "vote";
        $status = "failed";
        $platformId = $this->viaPromocodeService->getPlatforms();

        $promotion = $this->viaPromocodeService->getPromotionById($id);
        if (!$promotion) {
            return $this->errorResponse('Promotion not found.', 'Promotion not found.', 404);
        }

        $promocode = $this->promoCodeRepository->getPromoCodeByPromotionIdAndByPromocode($id, $promocodeInput);
        if (!$promocode) {
            return $this->errorResponse('Promocode not found.', 'Promocode not found.', 404);
        }

        $prizeId = null;
        $today = Carbon::today();
        if ($promocode->is_used) {
            $action = "claim";
            $status = "blocked";
        } else {
            if ($promotion->is_prize) {
                // 1. Auto prize check with active prize and daily limit
                $prizePromo = PrizePromo::with(['prize.message', 'prize.prizeUsers'])
                    ->where('promo_code_id', $promocode->id)
                    ->whereHas('prize', function ($q) use ($today) {
                        $q->where('is_active', true)
                            ->whereHas('prizeUsers', function ($query) use ($today) {
                                $query->whereDate('created_at', $today);
                            }, '<', DB::raw('daily_limit'));
                    })
                    ->first();

                if ($prizePromo) {
                    $action = "auto_win";
                    $status = "won";
                    $prizeId = $prizePromo->prize->id;
                    Queue::connection('rabbitmq')->push(new PrizePromoUpdateJob($prizePromo->id));
                } else {
                    // 2. Smart prize evaluation
                    $smartPrize = Prize::where('promotion_id', $id)
                        ->whereHas('category', fn($q) => $q->where('name', 'smart_random'))
                        ->with(['smartRandomValues.rule'])
                        ->get();

                    foreach ($smartPrize as $prize) {
                        $isValid = true;
                        foreach ($prize->smartRandomValues as $ruleValue) {
                            $method = match ($ruleValue->rule->key) {
                                'code_length'          => 'checkCodeLength',
                                'uppercase_count'      => 'checkUppercaseCount',
                                'lowercase_count'      => 'checkLowercaseCount',
                                'digit_count'          => 'checkDigitCount',
                                'special_char_count'   => 'checkSpecialCharCount',
                                'starts_with'          => 'checkStartsWith',
                                'not_starts_with'      => 'checkNotStartsWith',
                                'ends_with'            => 'checkEndsWith',
                                'not_ends_with'        => 'checkNotEndsWith',
                                'contains'             => 'checkContains',
                                'not_contains'         => 'checkNotContains',
                                'contains_sequence'    => 'checkContainsSequence',
                                'unique_char_count'    => 'checkUniqueCharCount',
                                default                => null
                            };

                            if (!$method || !method_exists($this->viaPromocodeService, $method)) {
                                Log::warning("Unknown rule method for key: {$ruleValue->rule->key}");
                                $isValid = false;
                                break;
                            }

                            if (!$this->viaPromocodeService->{$method}(
                                $promocode->promocode,
                                $ruleValue->operator,
                                json_decode($ruleValue->values, true)
                            )) {
                                $isValid = false;
                                break;
                            }
                        }

                        if ($isValid) {
                            $validPrize = $prize;
                            $action = "auto_win";
                            $status = "won";
                            $prizeId = $prize->id;
                            break;
                        }
                    }

                    // 3. Manual prize fallback
                    if (!$prizeId) {
                        $manualPrizeExists = Prize::where('promotion_id', $id)
                            ->whereHas('category', fn($q) => $q->where('name', 'manual'))
                            ->exists();

                        if ($manualPrizeExists) {
                            $action = "vote";
                            $status = "pending";
                        }
                    }
                }
            } else {
                $action = "vote";
                $status = "pending";
            }

            // Queue: consume promo and log action
            Queue::connection('rabbitmq')->push(new PromoCodeConsumeJob(
                $promocode->id,
                $user['id'],
                $platformId,
                receiptId: $receiptId ?? null,
                promotionProductId: $promotionProductId ?? null,
                prizeId: $prizeId,
                subPrizeId: $subPrizeId ?? null,
            ));
        }


        Queue::connection('rabbitmq')->push(new CreatePromoActionJob([
            'promotion_id' => $promotion->id,
            'promo_code_id' => $promocode->id,
            'user_id' => $user['id'],
            'prize_id' => $prizeId,
            'action' => $action,
            'status' => $status,
            'attempt_time' => now(),
            'message' => null,
        ]));
        if ($action == "claim") {
            return $this->errorResponse('Promocode avval foydalanilgan.', 'Promocode avval foydalanilgan.', 422);
        } else {
            return $this->successResponse(['status' => $status, 'action' => $action, 'prize_id' => $prizeId], 'Success');
        }
    }

    public function viaReceipt(Request $request, $promotionId)
    {
        return $this->successResponse(['promotions' => "success2"], "success");
    }

    public function checkStatus(Request $request, $promotionId)
    {
        return $this->successResponse(['promotions' => "success3"], "success");
    }
}
