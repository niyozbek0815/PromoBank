<?php

namespace App\Telegram\Handlers\Register;

use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class NameStepHandler
{
    public function ask($chatId)
    {
        Cache::store('redis')->put("tg_reg_state:$chatId", 'waiting_for_name', now()->addDays(7));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "👤 Iltimos, ismingizni kiriting"
        ]);
    }

    public function handle($chatId, $name)
    {
        Cache::store('redis')->put("tg_reg_data:$chatId:name", $name);
        return app(Phone2StepHandler::class)->ask($chatId);
    }
}
