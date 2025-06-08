<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;
    public $message;

    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle()
    {
        // Bu yerda SMS yuborish uchun xabar notification-service ga yuborilishi kerak
        // Ammo promo-service faqat job ni queue ga yuboradi,
        // real SMS yuborish notification-service da amalga oshiriladi,
        // shuning uchun handle() ni bo‘sh qoldirish yoki boshqa maqsadlar uchun ishlatishingiz mumkin.
    }
}
