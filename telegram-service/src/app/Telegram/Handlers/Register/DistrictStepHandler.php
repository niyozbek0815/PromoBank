<?php

namespace App\Telegram\Handlers\Register;

use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class DistrictStepHandler
{
    public function ask($chatId)
    {
        Cache::store('redis')->put("tg_reg_state:$chatId", 'waiting_for_district', now()->addDays(7));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "🏘 Iltimos, tumaningizni tanlang",
            // 'reply_markup' => json_encode([...])
        ]);
    }

    public function handle($chatId, $districtId)
    {
        Cache::store('redis')->put("tg_reg_data:$chatId:district_id", $districtId);
        return app(NameStepHandler::class)->ask($chatId);
    }
}
