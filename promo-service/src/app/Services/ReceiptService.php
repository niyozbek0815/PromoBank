<?php

namespace App\Services;

use App\Jobs\CreateReceiptAndProductJob;
use App\Models\Prize;
use App\Models\PromotionShop;
use App\Repositories\PlatformRepository;
use App\Repositories\PrizeMessageRepository;
use App\Repositories\PromotionMessageRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

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

    public function process($req, $user)
    {
        $lang = $req['lang'];
        $entries = collect();
        $message = null;
        $action = "vote";
        $status = "failed";
        $selectedPrize = null;
        $selectedProductId = null;
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
            $promotion = $shop->promotion;
            $prizes = Prize::where('promotion_id', $shop->promotion_id)
                ->withCount(['prizeUsers as today_prize_users_count' => function ($query) use ($today) {
                    $query->whereDate('created_at', $today);
                }])
                ->whereHas(
                    'category',
                    fn($q) =>
                    $q->whereIn('name', [
                        'weighted random',
                        'manual'
                    ])
                )
                ->where('is_active', true)
                ->whereColumn('awarded_quantity', '<', 'quantity')
                ->with('category:id,name') // category.name kerak bo'ladi
                ->orderBy('index', 'asc')
                ->get()->filter(fn($prize) => $prize->today_prize_users_count < $prize->daily_limit);;
            $checkProducts = collect($req['products']);
            $groupedPrizes = $prizes->groupBy(fn($prize) => $prize->category->name);
            $weighted_prizes =  $groupedPrizes['weighted random'] ?? collect();
            $manual_prizes = $groupedPrizes['manual'] ?? collect();
            $promoProductMap = collect($shop->products)
                ->keyBy(fn($product) => Str::lower($product->name));
            if ($weighted_prizes->isNotEmpty()) {
                $entries =  $this->getWeightedRandomPrize($weighted_prizes, $shop, $checkProducts, $promoProductMap, $today, $lang, $action, $status, $message, $selectedPrize, $selectedProductId);
            } elseif ($manual_prizes->isNotEmpty()) {
                $this->getManualPrize($promotion, $lang, $action, $status, $message);
            }
        } else {
            // men shu yerda  xecha qanday aksiyada qatnashmayotgan chek uchun fag;batlantiruvchi bal beraman
            $this->giveEncouragementPoints($user['id'], $req['name']);
        }

        Queue::connection(name: 'rabbitmq')->push(new CreateReceiptAndProductJob(
            $req,
            $user,
            null,
            $platformId,
            $selectedProductId ?? null,
            $selectedPrize['id'] ?? null,
            $subPrizeId ?? null,
            $status,
            $promotion->id ?? null,
            $entries->count() ?? 0
        ));
        return [
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'prize' => $selectedPrize ?? null,
        ];
    }
    private function giveEncouragementPoints($userId, $shopName)
    {
        // bu yerda ball hisoblash va yozish logikasi bo'lishi mumkin
        // masalan: 10 ball har aksiya yo‘q bo‘lsa

        $points = 10;

        // Ballarni saqlash logikasi — misol tariqasida log yozamiz
        \Log::info("User {$userId} received encouragement points", [
            'points' => $points,
            'reason' => "No active promotion for shop '{$shopName}'",
        ])

        // Agar sizda points saqlanadigan model bo‘lsa:
        // UserPoint::create([
        //     'user_id' => $userId,
        //     'points' => $points,
        //     'reason' => 'no_promotion_check',
        //     'source' => $shopName,
        // ]);
    }
    private function getWeightedRandomPrize($prizes, $shop, $checkProducts, $promoProductMap, $today, $lang, &$action, &$status, &$message, &$selectedPrize, &$selectedProductId)
    {
        $entries = collect();

        foreach ($checkProducts as $checkProduct) {
            $checkName = Str::lower($checkProduct['name']);
            foreach ($promoProductMap as $promoName => $promoProduct) {
                if (str_contains($checkName, $promoName)) {
                    for ($i = 0; $i < $checkProduct['count']; $i++) {
                        $entries->push([
                            'shop_id'      => $shop->id,
                            'product_id'   => $promoProduct->id,
                            'product_name' => $promoProduct->name,
                            'summa'        => $checkProduct['summa'],
                        ]);
                    }
                }
            }
        }
        if ($entries->isEmpty()) {
            $this->failResponse($shop->promotion, $lang, $action, $status, $message);
        } else {

            foreach ($prizes as $prize) {
                foreach ($entries as $entry) {
                    if (random_int(1, $prize->probability_weight) === 1) {

                        $selectedPrize = $prize;
                        $selectedPrize->increment('awarded_quantity');
                        $selectedProductId = $entry['product_id'];
                        $action = "auto_win";
                        $status = "won";
                        $message = $this->getPrizeMessage($selectedPrize, $lang);
                        break; // Birinchi mos tushganini tanlaymiz
                    }
                }
            }
        }
        return $entries;
    }

    private function getManualPrize($promotion, $lang, &$action, &$status, &$message,)

    {
        $action = "vote";
        $status = "pending";
        $message = $this->getPromotionMessage($promotion->id, $lang, 'success');
    }
    private function getPlatforms()
    {
        return  Cache::store('redis')->remember('platform:mobile:id', now()->addMinutes(60), function () {
            return $this->platformRepository->getPlatformGetId('mobile');
        });
    }
    private function getPrizeMessage($prize, $lang)
    {
        $message = $this->prizeMessageRepository->getMessageForPrize($prize, 'mobile', 'success');
        return $message ? $message->getTranslation('message', $lang) : "Tabriklaymiz siz {$prize->name} yutdingiz.";
    }
    private function failResponse($promotion, $lang, &$action, &$status, &$message)
    {
        $action = "claim";
        $status = "failed";
        $prize = null;
        $message = $this->getPromotionMessage($promotion->id, $lang, 'fail');
    }
    private function getPromotionMessage($promotionId, $lang, $type): string
    {
        $message = $this->promotionMessageRepository->getMessageForPromotion($promotionId, 'mobile', $type);
        return $message->getTranslation('message', $lang);
    }
}
