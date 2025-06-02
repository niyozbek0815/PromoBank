<?php

namespace App\Jobs;

use App\Services\ViaPromocodeFromSms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessSmsPromoJob implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(ViaPromocodeFromSms $viaPromocodeFromSms)
    {
        $phone = $this->data['phone'] ?? null;

        if (!$phone) {
            logger()->warning('Telefon raqam topilmadi', $this->data);
            return;
        }

        try {
            $message = $viaPromocodeFromSms->viaPromocode($this->data);
            logger()->info('Promo code qabul qilindi', $message);
        } catch (\Throwable $e) {
            logger()->error('ProcessSmsPromoJob exception', [
                'message' => $e->getMessage(),
                'data' => $this->data,
            ]);
        }
    }
}
