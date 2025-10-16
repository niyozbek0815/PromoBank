<?php
namespace App\Services;

use App\Jobs\CreatePromoActionJob;
use App\Jobs\PrizePromoUpdateJob;
use App\Jobs\PromoCodeConsumeJob;
use App\Models\Messages;
use App\Models\Prize;
use App\Models\PrizePromo;
use App\Repositories\PlatformRepository;
use App\Repositories\PromoCodeRepository;
use App\Repositories\PromotionRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class ViaPromocodeService
{

    public function __construct(
        private PromotionRepository $promotionRepository,
        private PromoCodeRepository $promoCodeRepository,
        private PlatformRepository $platformRepository,
        private SmartPrizeValidatorService $smartPrizeValidator,
    ) {
    }



    public function proccess($req, $user, $id, $platform_name)
    {
        $promocodeInput = $req['promocode'];
        $lang = $req['lang'] ?? 'uz';
        $today = Carbon::today();
        $action = "invalid";
        $status = "fail";
        $message = null;
        $platformId = $this->getPlatformId($platform_name);
        $promotion = $this->getPromotionById($id);
        if (!$promotion) {
            return ['promotion' => true];
        }
        $promocode = $this->promoCodeRepository->getPromoCodeByPromotionIdAndByPromocode($id, $promocodeInput);
        if (!$promocode) {
            $status = "invalid";
            $message = $this->getMessage($promotion->id, null, $lang, $status, $promocodeInput, $platform_name);
        } else {
            if ($promocode->is_used) {
                $action = "block";
                $status = "claim";
                $message = $this->getMessage($promotion->id, null, $lang, $status, $promocodeInput, $platform_name);
            } else {
                if (in_array($promotion->winning_strategy, ['immediate', 'hybrid'])) {
                    $wonPrize = $this->handlePrizeEvaluation($promocode, $promotion, $today, $lang, $platform_name, $status, $message, $action);
                }
                if ($status !== 'win') {
                    if (in_array($promotion->winning_strategy, ['delayed', 'hybrid'])) {
                        $action = 'manual_win';
                        $status = 'pending';
                    } else {
                        $action = 'no_win';
                        $status = 'lose';
                    }
                    $message = $this->getMessage(
                        $promotion->id,
                        null,
                        $lang,
                        $status,
                        $promocodeInput,
                        $platform_name
                    );
                }
                Queue::connection('rabbitmq')->push(new PromoCodeConsumeJob(
                    promoCodeId: $promocode->id,
                    userId: $user['id'],
                    platformId: $platformId,
                    receiptId: $receiptId ?? null,
                    promotionProductId: $promotionProductId ?? null,
                    prizeId: $wonPrize['id'] ?? null,
                    subPrizeId: $subPrizeId ?? null,
                    promotionId: $id
                ));
            }
            Queue::connection('rabbitmq')->push(new CreatePromoActionJob([
                'promotion_id' => $promotion->id,
                'promo_code_id' => $promocode->id,
                'platform_id' => $platformId,
                'receipt_id'=>null,
                'shop_id'=>null,
                'user_id' => $user['id'],
                'prize_id' => $wonPrize['id'] ?? null,
                'action' => $action,
                'status' => "promocode_" . $status,
                'attempt_time' => now(),
                'message' => null,
            ]));
        }


        return [
            'status' => $status,
            'message' => $message,
        ];
    }
    public function getPromotionById($id)
    {
        $cacheKey = 'HasPromotion:mobile' . $id;
        $ttl = now()->addMinutes(5); // 5 daqiqa kesh
        return Cache::store('redis')->remember($cacheKey, $ttl, function () use ($id) {
            return $this->promotionRepository->getPromotionByIdforVia($id, ['text_code', 'qr_code']);
        });
    }
    private function getPlatformId(string $platformCode = 'mobile'): ?int
    {
        return Cache::store('redis')->remember(
            "platform:{$platformCode}:id",
            now()->addMinutes(60),
            fn() => $this->platformRepository->getPlatformGetId($platformCode)
        );
    }
    private function handlePrizeEvaluation($promocode, $promotion, $today, $lang, $platform_name, &$status, &$message, &$action)
    {
        // 1. Auto prize



        $prizePromo = PrizePromo::whereHas('prize', function ($q) use ($today) {
            $q->where('is_active', true)
                ->whereRaw('
          (
              SELECT COUNT(*)
              FROM promo_code_users AS pu
              WHERE pu.prize_id = prizes.id
              AND DATE(pu.created_at) = ?
          ) < prizes.daily_limit
      ', [$today]);
        })
            ->with(['prize', 'prize.prizeUsers'])
            ->first();
        if ($prizePromo) {
            $action = "auto_win";
            $status = "win";
            $wonPrize = $prizePromo->prize;
            $message = $this->getMessage($promotion->id, ['id' => $wonPrize['id'], 'name' => $wonPrize['name']], $lang, $status, promocode: $promocode['promocode']);
            Queue::connection('rabbitmq')->push(new PrizePromoUpdateJob($prizePromo->id));
        }
        $smartPrizes = Prize::where('promotion_id', $promotion->id)
            ->whereHas('category', fn($q) => $q->where('name', 'smart_random'))
            ->withCount([
                'prizeUsers as today_prize_users_count' => function ($query) use ($today) {
                    $query->whereDate('created_at', $today);
                }
            ])
            ->where('is_active', true)
            ->whereColumn('awarded_quantity', '<', 'quantity')
            ->with(['smartRandomValues.rule'])->orderBy('index', 'asc')->get()->filter(fn($prize) => $prize->today_prize_users_count < $prize->daily_limit);
        foreach ($smartPrizes as $prize) {
            if ($this->smartPrizeValidator->validate($prize, $promocode->promocode)) {
                $action = "smart_win";
                $status = "win";
                $wonPrize = $prize;
                $message = $this->getMessage($promotion->id, ['id' => $wonPrize['id'], 'name' => $wonPrize['name']], $lang, $status, promocode: $promocode['promocode']);
                break;
            }
        }
        return isset($wonPrize) ? $wonPrize : null;

    }
    private function getMessage(int $promotionId, ?array $prize, string $lang, string $status, string $promocode, ?string $channel = 'mobile')
    {
        $message = Messages::resolveLocalizedMessage('promo', [
            'status' => $status,
            'prize_id' => $prize['id'] ?? null,
            'promotion_id' => $promotionId,
            'lang' => $lang,
            'channel' => $channel,
        ]);

        if (!$message) {
            return null; // fallback topilmasa
        }

        // 3️⃣ Sovrin nomini aniqlaymiz (multi-lang yoki oddiy)
        $prizeName = match (true) {
            is_array($prize['name'] ?? null) => (
                $prize['name'][$lang]
                ?? $prize['name']['uz']
                ?? reset($prize['name'])
                ?? '—'
            ),
            is_string($prize['name'] ?? null) => $prize['name'],
            default => '—',
        };

        // 4️⃣ Dinamik tokenlarni real qiymatlar bilan almashtirish
        $replacements = [
            ':code' => $promocode,
            ':id' => $promotionId,
            ':prize' => $prizeName,
        ];

        $finalMessage = strtr($message, $replacements);

        // 5️⃣ Xabarni platformaga moslab formatlaymiz (masalan, SMS uchun max 160 belgi)
        if ($channel === 'sms') {
            return mb_strimwidth($finalMessage, 0, 160, '...');
        }

        return $finalMessage;
    }


}
