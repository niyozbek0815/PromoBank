<?php

namespace App\Jobs;

use App\Models\PromoCode;
use App\Services\ViaPromocodeFromSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSmsPromoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 90;

    public function __construct(
        public readonly string $phone,
        public readonly string $promocode,
        public readonly string $shortPhone,
        public readonly string $correlationId,
        public readonly string $createdAt,
    ) {
        // ✅ Laravelning ichki traitini ishlatamiz
        $this->onQueue('promo_queue');
    }

    public function handle(ViaPromocodeFromSms $viaPromocodeFromSms): void
    {
        try {
            $platformId = $viaPromocodeFromSms->getPlatforms();
            $baseUrl = config('services.urls.auth_service');

            $promo = PromoCode::with(['promotion.participationTypesSms'])
                ->where('promocode', $this->promocode)
                ->first();

            if (!$promo) {
                $message = $viaPromocodeFromSms->getMessage(0, null, 'invalid', $this->promocode);
            } else {
                $promotion = $promo->promotion;
                $expectedShortPhone = optional($promotion->participationTypesSms->first())->additional_rules
                    ? json_decode($promotion->participationTypesSms->first()->additional_rules, true)['phone'] ?? null
                    : null;

                if ($expectedShortPhone && $expectedShortPhone !== $this->shortPhone) {
                    return;
                }

                $result = $viaPromocodeFromSms->proccess($baseUrl, $this->shortPhone, $promo, $promotion, $platformId);
                $message = $result['message'] ?? null;
            }

            if (!empty($message)) {
                Log::info('[SMS_PROMO] Message sent', ['phone' => $this->phone, 'message' => $message]);

                SendSmsNotification::dispatch(
                    $this->phone,
                    $message,
                    $this->correlationId
                )->onQueue('notification_queue');
            }
        } catch (\Throwable $e) {
            Log::error('❌ [SMS_PROMO] Job failed', [
                'correlation_id' => $this->correlationId,
                'phone' => $this->phone,
                'promocode' => $this->promocode,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }
}
