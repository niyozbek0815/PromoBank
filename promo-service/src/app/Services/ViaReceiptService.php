<?php

namespace App\Services;

use App\Jobs\CreateReceiptAndProductJob;
use App\Models\Prize;
use App\Models\PromotionShop;
use App\Repositories\PlatformRepository;
use App\Repositories\PrizeMessageRepository;
use App\Repositories\PromotionMessageRepository;
use App\Repositories\PromotionRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class ViaReceiptService
{
    public function __construct(
        private ViaPromocodeService $viaPromocodeService,
        private PlatformRepository $platformRepository,
        private PromotionRepository $promotionRepository,
        private PrizeMessageRepository $prizeMessageRepository,
        private PromotionMessageRepository $promotionMessageRepository,
    ) {
        $this->viaPromocodeService = $viaPromocodeService;
        $this->platformRepository = $platformRepository;
        $this->prizeMessageRepository = $prizeMessageRepository;
        $this->promotionRepository = $promotionRepository;
    }

    public function process($req, $user, $id)
    {

        $lang = $req['lang'];
        $message = null;
        $action = "vote";
        $status = "failed";
        $selectedPrize = null;
        $selectedProductId = null;
        $today = Carbon::today();
        $promotion_boolean = false;
        $platformId = $this->getPlatforms();
        $promotion = $this->getPromotionById($id);
        if (!$promotion) {
            $promotion_boolean = true;
        } else {
            if ($promotion->is_prize) {
                $this->handlePrizeEvaluation($req['name'], collect($req['products']), $promotion, $today, $lang, $action, $status, $message, $selectedPrize, $selectedProductId);
            }
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
        ));
        return [
            'action' => $action,
            'status' => $status,
            'promotion' => $promotion_boolean,
            'message' => $message,
            'prize' => $selectedPrize ?? null,
        ];
    }
    private function handlePrizeEvaluation($shopname, $checkProducts, $promotion, $today, $lang, &$action, &$status, &$message, &$selectedPrize, &$selectedProductId)
    {
        $prizes = Prize::where('promotion_id', $promotion->id)
            ->where('is_active', true)
            ->whereColumn('awarded_quantity', '<', 'quantity') // sovg'a tugamagan bo'lishi kerak
            ->whereHas(
                'category',
                fn($q) =>
                $q->where('name', 'weighted random') // faqat 'weighted random' kategoriyadagi sovg'alar
            )
            ->orderBy('index', 'asc')->get();
        $shop = PromotionShop::with('products:id,name')
            ->where('name', $shopname)
            ->where('promotion_id', $promotion->id)
            ->with('products')
            ->first();
        if ($prizes->isEmpty() || !$shop) {
            $this->failResponse($promotion, $lang, $action, $status, $message);
        } else {
            // Mahsulot nomlarini indekslash (case-insensitive)
            $promoProductMap = collect($shop->products)
                ->keyBy(fn($product) => Str::lower($product->name));

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
                $this->failResponse($promotion, $lang, $action, $status, $message);
            } else {

                foreach ($prizes as $prize) {
                    foreach ($entries as $entry) {
                        if (random_int(1, $prize->probability_weight) === 1) {
                            $selectedPrize = $prize;
                            $selectedProductId = $entry['product_id'];
                            break; // Birinchi mos tushganini tanlaymiz
                        }
                    }
                }
            }
        }
        if ($selectedPrize !== null) {
            $action = "auto_win";
            $status = "won";
            $message = $this->getPrizeMessage($selectedPrize, $lang);
        } else {
            $hasManualPrize = Prize::where('promotion_id', $promotion->id)
                ->whereHas('category', fn($q) => $q->where('name', 'manual'))->exists();
            if ($hasManualPrize) {
                $action = "vote";
                $status = "pending";
                $message = $this->getPromotionMessage($promotion->id, $lang, 'success');
            } else {
                $this->failResponse($promotion, $lang, $action, $status, $message);
            }
        }
    }
    private function failResponse($promotion, $lang, &$action, &$status, &$message)
    {
        $action = "claim";
        $status = "failed";
        $prize = null;
        $message = $this->getPromotionMessage($promotion->id, $lang, 'fail');
    }
    private function getPlatforms()
    {
        return  Cache::store('redis')->remember('platform:mobile:id', now()->addMinutes(60), function () {
            return $this->platformRepository->getPlatformGetId('mobile');
        });
    }
    private function getPromotionById($id)
    {
        $cacheKey = 'HasPromotion:mobile' . $id;
        $ttl = now()->addMinutes(5); // 5 daqiqa kesh
        return Cache::store('redis')->remember($cacheKey, $ttl, function () use ($id) {
            return  $this->promotionRepository->getPromotionByIdforVia($id, ['receipt_scan']);
        });
    }
    private function getPrizeMessage($prize, $lang)
    {
        $message = $this->prizeMessageRepository->getMessageForPrize($prize, 'mobile', 'success');
        return $message ? $message->getTranslation('message', $lang) : "Tabriklaymiz siz {$prize->name} yutdingiz.";
    }
    private function getPromotionMessage($promotionId, $lang, $type): string
    {
        $message = $this->promotionMessageRepository->getMessageForPromotion($promotionId, 'mobile', $type);
        return $message->getTranslation('message', $lang);
    }
}
