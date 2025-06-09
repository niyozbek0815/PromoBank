<?php

namespace App\Jobs;

use App\Services\NotificationSender;
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

    public $phone;
    public $promocode;
    public $shortphone;
    public $correlationId, $created_at;


    public function __construct(string $phone, $promocode, $shortphone, $correlationId, $created_at)
    {
        $this->phone = $phone;
        $this->promocode = $promocode;
        $this->shortphone = $shortphone;
        $this->correlationId = $correlationId;
        $this->created_at = $created_at;
    }

    public function handle(ViaPromocodeFromSms $viaPromocodeFromSms)
    {
        Log::info('ProcessSmsPromoJob started in promo-service', ['phone' => $this->phone]);

        $data = [
            'phone' => $this->phone,
            'promo_code' => $this->promocode,
            'short_phone' => $this->shortphone,
        ];

        try {
            $response = $viaPromocodeFromSms->viaPromocode($data);

            if (!empty($response['message'])) {
                SendSmsNotification::dispatch($this->phone, $response['message'])
                    ->onQueue('notification_queue');
                Log::info('SendSmsNotification dispatched from promo-service', [
                    'phone' => $this->phone,
                    'message' => $response['message'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error in ProcessSmsPromoJob', [
                'error' => $e->getMessage(),
                'phone' => $this->phone,
                'promo_code' => $this->promocode,
            ]);
            throw $e;
        }
    }
}
