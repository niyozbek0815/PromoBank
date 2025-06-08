<?php

namespace App\Services;

use App\Jobs\SendSmsNotification;
use Illuminate\Support\Facades\Http;

class SmsSendService
{

    public function sendMessage(string $otp, string $phone)
    {
        $message = "Promobank. Sizning ro'yhatdan o'tish kodingiz:" . $otp . "  Your registration code:" . $otp;
        SendSmsNotification::dispatch($phone, $message)
            ->onQueue('notification_queue');
    }
}
