<?php

namespace App\Telegram\Handlers\Register;

use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class Phone2StepHandler
{
    public function ask($chatId)
    {
        Cache::store('redis')->put("tg_reg_state:$chatId", 'waiting_for_phone2', now()->addDays(7));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "📞 Ixtiyoriy qo‘shimcha telefon raqam kiriting yoki ‘Yo‘q’ deb yozing"
        ]);
    }

    public function handle($chatId, $phone2)
    {
        if (strtolower($phone2) === 'yo‘q') $phone2 = null;
        Cache::store('redis')->put("tg_reg_data:$chatId:phone2", $phone2);
        return app(GenderStepHandler::class)->ask($chatId);
    }
}
