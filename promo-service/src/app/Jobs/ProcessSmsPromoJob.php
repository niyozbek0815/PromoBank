<?php

namespace App\Jobs;

use App\Services\NotificationSender;
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
            $response = $message = $viaPromocodeFromSms->viaPromocode($this->data);
            new NotificationSender()->send([
                'phone' => $phone,
                'message' => $response['nessage']

            ]);
            logger()->info('Promo code qabul qilindi', $message);
        } catch (\Throwable $e) {
            logger()->error('ProcessSmsPromoJob exception', [
                'message' => $e->getMessage(),
                'data' => $this->data,
            ]);
        }
    }
}
