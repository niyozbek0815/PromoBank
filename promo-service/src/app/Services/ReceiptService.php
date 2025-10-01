<?php

namespace App\Services;

use App\Jobs\CreateReceiptAndProductJob;
use App\Models\Prize;
use App\Models\PromotionShop;
use App\Models\UserPointBalance;
use App\Repositories\PlatformRepository;
use App\Repositories\PrizeMessageRepository;
use App\Repositories\PromotionMessageRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Termwind\Components\BreakLine;

class ReceiptService
{

    public function __construct(
        private PlatformRepository $platformRepository,
        private PromotionMessageRepository $promotionMessageRepository,
        private PrizeMessageRepository $prizeMessageRepository,
    ) {
        $this->platformRepository = $platformRepository;
        $this->promotionMessageRepository = $promotionMessageRepository;
        $this->prizeMessageRepository = $prizeMessageRepository;
    }

    public function proccess($req, $user)
    {
        $lang = $req['lang'];
        $entries = collect();
        $message = [];
        $action = null;
        $status = 'success';
        $encouragementPoints = null;
        $selectedPrizes = [];
        $menualPrizeCount = 0;
        $today = Carbon::today();
        $platformId = $this->getPlatforms();
        $shop = PromotionShop::with('products:id,name')
            ->where('name', $req['name'])
            ->with(['products', 'promotion'])
            ->whereHas('promotion', function ($query) use ($today) {
                $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
            })
            ->first();
        if ($shop) {
            Log::info('Found shop: ', ['shop' => $shop]);

            $promotion = $shop->promotion;
            $prizes = Prize::where('promotion_id', $shop->promotion_id)
                ->withCount([
                    'prizeUsers as today_prize_users_count' => function ($query) use ($today) {
                        $query->whereDate('created_at', $today);
                    }
                ])
                ->whereHas(
                    'category',
                    fn($q) =>
                    $q->whereIn('name', [
                        'weighted_random',
                        'manual'
                    ])
                )
                ->where('is_active', true)
                ->whereColumn('awarded_quantity', '<', 'quantity')
                ->with('category:id,name') // category.name kerak bo'ladi
                ->orderBy('index', 'asc')
                ->get()->filter(fn($prize) => $prize->today_prize_users_count < $prize->daily_limit);
            Log::info('Filtered prizes: ', ['prizes' => $prizes]);
            $checkProducts = collect($req['products']);
            $groupedPrizes = $prizes->groupBy(fn($prize) => $prize->category->name);
            $weighted_prizes = $groupedPrizes['weighted_random'] ?? collect();
            $manual_prizes = $groupedPrizes['manual'] ?? collect();
            Log::info('Weighted prizes: ', ['weighted_prizes' => $weighted_prizes]);
            Log::info('Manual prizes: ', ['manual_prizes' => $manual_prizes]);
            $promoProductMap = collect($shop->products)
                ->keyBy(fn($product) => Str::lower($product->name));
            $entries = $this->getEntries($checkProducts, $shop, $promoProductMap);

            foreach ($entries as $entry) {
                $selected = false;
                if ($weighted_prizes->isNotEmpty()) {
                    Log::info('Trying to get weighted random prize for entry: ', $entry);
                    $this->getWeightedRandomPrize($weighted_prizes, $entry, $selectedPrizes, $selected);
                }
                if ($manual_prizes->isNotEmpty() && !$selected) {
                    $this->getManualPrize($menualPrizeCount);
                }
                $selected = false;
            }
            $this->returnMessage($promotion, $menualPrizeCount, $selectedPrizes, $lang, $action, $status, $message, $encouragementPoints);

        } else {
            $this->giveEncouragementPoints($action, $message);
        }

        Queue::connection(name: 'rabbitmq')->push(new CreateReceiptAndProductJob(
            $req,
            $user,
            null,
            $platformId,
            $selectedPrizes ?? null,
            $subPrizeId ?? null,
            $menualPrizeCount,
            $promotion['id'] ?? null,
        ));



        return [
            'action' => $action,
            'status' => $status,
            'message' => $message,
        ];
    }
    public function getPoints($user)
    {
        return UserPointBalance::where('user_id', $user['id'])->value('balance') ?? 0;
    }
    private function returnMessage($promotion, $menualPrizeCount, $selectedPrizes, $lang, &$action, &$status, &$message, &$encouragementPoints)
    {
        $status = "success";

        if ($menualPrizeCount == 0 && count($selectedPrizes) == 0) {
            $this->giveEncouragementPoints($action, $message);
        } else {
            $action = "won";
            if ($selectedPrizes) {
                foreach ($selectedPrizes as $selectedPrize) {
                    $prize = $selectedPrize['prize'];
                    $message[] = $this->getPrizeMessage($prize, $lang);
                }
            }
            if ($menualPrizeCount > 0) {
                // $message[] =  $this->getPromotionMessage(
                //     $promotion->id,
                //     $lang,
                //     'manual_win',
                //     ['{count}' => $menualPrizeCount]
                // );
                $message[] = "Siz {$menualPrizeCount} ta manual sovrin yutdingiz.";
            }
        }
    }
    private function giveEncouragementPoints(&$action, &$message)
    {
        $encouragementPoints = config('services.constants.encouragement_points');
        $message[] = "Siz {$encouragementPoints} promobal oldingiz. yana Skanerlang va promobalarni yig'ishda davom eting!";
        $action = "points_vote";
    }
    private function getEntries($checkProducts, $shop, $promoProductMap)
    {
        $entries = collect();

        foreach ($checkProducts as $checkProduct) {
            $checkName = Str::lower($checkProduct['name']);
            foreach ($promoProductMap as $promoName => $promoProduct) {
                if (str_contains($checkName, $promoName)) {
                    $count = ($checkProduct['count'] > 5) ? 5 : $checkProduct['count'];
                    for ($i = 0; $i < $count; $i++) {
                        $entries->push([
                            'shop_id' => $shop->id,
                            'product_id' => $promoProduct->id,
                            'product_name' => $promoProduct->name,
                            'summa' => $checkProduct['summa'],
                        ]);
                    }
                }
            }
        }
        return $entries;
    }
    private function getWeightedRandomPrize($prizes, $entry, &$selectedPrizes, &$selected)
    {
        foreach ($prizes as $prize) {
            if (random_int(1, $prize->probability_weight) === 1) {
                $selectedPrizes[] = [
                    'entry' => $entry,
                    'prize' => $prize,
                ];
                $selected = true;
                Log::info("Prize selected: {$prize->name}", ['probability_weight' => $prize->probability_weight]);
                break;
            }
        }
    }

    private function getManualPrize(&$menualPrizeCount)
    {
        $menualPrizeCount++;
    }
    private function getPlatforms()
    {
        return Cache::store('redis')->remember('platform:mobile:id', now()->addMinutes(60), function () {
            return $this->platformRepository->getPlatformGetId('mobile');
        });
    }
    private function getPrizeMessage($prize, $lang)
    {
        // $message = $this->prizeMessageRepository->getMessageForPrize($prize, 'mobile', 'success');
        // return $message ? $message->getTranslation('message', $lang) : "Tabriklaymiz siz {$prize->name} yutdingiz.";
        return "Tabriklaymiz siz {$prize->name} yutdingiz.";
    }
    private function failResponse($promotion, $lang, &$action, &$status, &$message)
    {
        $action = "claim";
        $status = "failed";
        $prize = null;
        $message = $this->getPromotionMessage($promotion->id, $lang, 'fail');
    }
    private function getPromotionMessage($promotionId, $lang, $type, array $placeholders = []): string
    {
        $message = $this->promotionMessageRepository
            ->getMessageForPromotion($promotionId, 'mobile', $type)
                ?->getTranslation('message', $lang) ?? '';

        return strtr($message, $placeholders);
    }
}
