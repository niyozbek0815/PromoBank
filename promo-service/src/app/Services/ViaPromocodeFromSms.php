<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Prize;
use App\Models\Platform;
use App\Models\PrizePromo;
use App\Jobs\PrizePromoUpdateJob;
use App\Jobs\PromoCodeConsumeJob;
use App\Jobs\CreatePromoActionJob;
use App\Models\Messages;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class ViaPromocodeFromSms
{
    public function __construct(
        private FromServiceRequest $forwarder,
        private SmartPrizeValidatorService $smartPrizeValidator,
    ) {
    }

    public function proccess($baseUrl, $phone, $promo, $promotion, $platformId)
    {
        $action = "invalid";
        $status = "fail";
        $message = null;
        $response = $this->forwarder->forward(
            'POST',
            $baseUrl,
            '/users_for_sms',
            ['phone' => $phone]
        );
        if ($response->successful()) {
            $user = data_get($response->json(), 'data.user');
            if (!$user) {
                $status = "invalid";
                logger()->error('User ID mavjud emas, lekin response successful', [
                    'response' => $response->json(),
                    'base_url' => $baseUrl,
                ]);
                return [
                    'action' => $action,
                    'status' => $status,
                    'message' => $message,
                ];
            }

            if ($promo->is_used) {
                $action = "block";
                $status = "claim";
                $message = $this->getMessage($promotion->id, null, $status, $promo->promocode);
            } else {
                if (in_array($promotion->winning_strategy, ['immediate', 'hybrid'])) {
                    $prize = $this->handlePrizeEvaluation($promo, $promotion, $action, $status, $message);
                }
                if ($status !== 'win') {
                    // Holat va action ni aniqlaymiz
                    [$action, $status] = in_array($promotion->winning_strategy, ['delayed', 'hybrid'])
                        ? ['manual_win', 'pending']
                        : ['no_win', 'lose'];

                    // Xabarni bitta chaqiruv bilan olamiz
                    $message = $this->getMessage(
                        $promotion->id,
                        null,
                        $status,
                        $promo['promocode']
                    );
                }
                Queue::connection('rabbitmq')->push(new PromoCodeConsumeJob(
                    promoCodeId: $promo->id,
                    userId: $user['id'],
                    platformId: $platformId,
                    receiptId: $receiptId ?? null,
                    promotionProductId: $promotionProductId ?? null,
                    prizeId: $prize['id'] ?? null,
                    subPrizeId: $subPrizeId ?? null,
                    promotionId: $promotion->id,
                ));
            }

            Queue::connection('rabbitmq')->push(new CreatePromoActionJob([
                'promotion_id' => $promotion->id,
                'promo_code_id' => $promo->id,
                'platform_id' => $platformId,
                'user_id' => $user['id'],
                'prize_id' => $prize['id'] ?? null,
                'action' => $action,
                'receipt_id' => null,
                'shop_id' => null,
                'status' => "sms_" . $status,
                'attempt_time' => now(),
                'message' => null,
            ]));
            return [
                'action' => $action,
                'status' => $status,
                'message' => $message,
            ];

        } else {
            logger()->error('Userni olishda xatolik', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }
    }
    public function getPlatforms()
    {
        return Cache::store('redis')->remember('platform:sms:id', now()->addMinutes(60), function () {
            return Platform::where('name', 'sms')->value('id');
        });
    }
    private function handlePrizeEvaluation($promocode, $promotion, &$action, &$status, &$message)
    {

        $prizePromo = PrizePromo::whereHas('prize', function ($q) use ($today) {
            $q->where('is_active', true)
                ->whereRaw('
          (
              SELECT COUNT(*)
              FROM promo_code_users AS pu
              WHERE pu.prize_id = prizes.id
              AND DATE(pu.created_at) = ?
          ) < prizes.daily_limit
      ', [Carbon::today()]);
        })
            ->with(['prize', 'prize.prizeUsers'])
            ->first();
        if ($prizePromo) {
            $action = "auto_win";
            $status = "win";
            $wonPrize = $prizePromo->prize;
            $message = $this->getMessage($promotion->id, ['id' => $wonPrize['id'], 'name' => $wonPrize['name']], $status, $promocode['promocode']);
            Queue::connection('rabbitmq')->push(new PrizePromoUpdateJob($prizePromo->id));
        }

        $smartPrizes = Prize::where('promotion_id', $promotion->id)
            ->whereHas('category', fn($q) => $q->where('name', 'smart_random'))
            ->with(['smartRandomValues.rule'])->orderBy('index', 'asc')->get();

        foreach ($smartPrizes as $smartprize) {
            if ($this->smartPrizeValidator->validate($smartprize, $promocode->promocode)) {
                $action = "smart_win";
                $status = "win";
                $wonPrize = $smartprize;
                $message = $this->getMessage($promotion->id, ['id' => $wonPrize['id'], 'name' => $wonPrize['name']], $status, $promocode['promocode']);
                break;
            }
        }
        return isset($wonPrize) ? $wonPrize : null;
    }
    public function getMessage(
        int $promotionId,
        ?array $prize,
        string $status,
        string $promocode
    ): ?string {
        // 1️⃣ Faqat SMS kanalidan xabarni olamiz
        $message = Messages::resolveLocalizedMessage('promo', [
            'status' => $status,
            'promotion_id' => $promotionId,
            'prize_id' => $prize['id'] ?? null,
            'channel' => 'sms',
        ]);

        if (empty($message) || !is_string($message)) {
            return null; // hech qanday SMS xabar topilmadi
        }

        // 2️⃣ Sovrin nomini soddalashtirilgan tarzda aniqlaymiz
        $prizeName = $prize['name'] ?? '—';

        // 3️⃣ Tokenlarni almashtirish (eng tez usul bilan)
        $replacements = [
            ':code' => $promocode,
            ':prize' => $prizeName,
            ':id' => $promotionId,
        ];

        $finalMessage = strtr($message, $replacements);

        // 4️⃣ SMS uzunligini nazorat qilish (160 belgigacha)
        return mb_strimwidth(trim($finalMessage), 0, 160, '...');
    }
}
