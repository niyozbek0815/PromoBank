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
    /**
     * Create a new job instance.
     */
    public $phone;
    public $promocode;
    public $shortphone;
    public $correlationId;
    public $created_at;

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
        Log::info(message: 'ProccesSmsPromoJob ishladi Promo-service ichida');

        $data = [
            'phone' => $this->phone,
            'promo_code' => $this->promocode,
            'short_phone' => $this->shortphone,
        ];


        $phone = $this->phone ?? null;
        if (!$phone) {
            return response()->json([
                'status' => 'error',
                'message' => 'Telefon raqam topilmadi',
            ]);
        }


        try {

            $response = $viaPromocodeFromSms->viaPromocode($data);
            SendSmsNotification::dispatch($phone, $response['message'])
                ->onQueue('notification_queue');
            Log::info('SendSmsNotificationga data yuborildi Promo-service ichidan:', ['phone' => $this->phone, 'message' => $this->message]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Xatolik yuz berdi: ' . $e->getMessage(),
            ]);
        }
    }
}
