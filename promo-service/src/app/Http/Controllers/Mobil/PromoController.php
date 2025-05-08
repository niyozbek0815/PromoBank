<?php

namespace App\Http\Controllers\Mobil;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Jobs\PromoCodeConsumeJob;
use App\Jobs\CreatePromoActionJob;
use App\Http\Controllers\Controller;
use App\Services\ViaPromocodeService;
use Illuminate\Support\Facades\Queue;
use App\Http\Resources\PromotionResource;
use App\Repositories\PromoCodeRepository;
use App\Repositories\PromotionRepository;
use App\Http\Requests\SendPromocodeRequest;

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
        $lang = $req['lang'];

        $action = "vote";
        $status = "failed";
        $prizeId = null;
        $today = Carbon::today();
        $platformId = $this->viaPromocodeService->getPlatforms();

        $promotion = $this->viaPromocodeService->getPromotionById($id);
        if (!$promotion) {
            return $this->errorResponse('Promotion not found.', 'Promotion not found.', 404);
        }
        $promocode = $this->promoCodeRepository->getPromoCodeByPromotionIdAndByPromocode($id, $promocodeInput);

        if (!$promocode) {
            return $this->errorResponse('Promocode not found.', 'Promocode not found.', 404);
        }
        // return $this->successResponse(['promotions' => $promocode], "success");

        if ($promocode->is_used) {
            $action = "claim";
            $status = "blocked";
            $message = $this->viaPromocodeService->getPromotionMessage($promotion->id, $lang, 'claim');
        } else {
            if ($promotion->is_prize) {
                $prizeId = $this->viaPromocodeService->handlePrizeEvaluation($promocode, $promotion, $today, $lang, $action, $status, $message);
            }

            // return $prizeId;

            if (!$promotion->is_prize || !$prizeId) {
                $action = "vote";
                $status = "pending";
                $message = $this->viaPromocodeService->getPromotionMessage($promotion->id, $lang, 'success');
            }

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

        return $action === "claim"
            ? $this->errorResponse($message, $message, 422)
            : $this->successResponse([
                'status' => $status,
                'action' => $action,
                'prize_id' => $prizeId,
            ], $message ?? "success");
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
