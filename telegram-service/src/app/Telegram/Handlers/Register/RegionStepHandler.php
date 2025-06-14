<?php
namespace App\Telegram\Handlers\Register;

use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class RegionStepHandler
{
    public function ask($chatId)
    {
        Cache::store('redis')->put("tg_reg_state:$chatId", 'waiting_for_region', now()->addDays(7));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => "📍 Iltimos, yashash hududingizni tanlang",
            // 'reply_markup' => json_encode([...]) // inline region list bo‘lsa
        ]);
    }

    public function handle($chatId, $regionId)
    {
        Cache::store('redis')->put("tg_reg_data:$chatId:region_id", $regionId);
        return app(DistrictStepHandler::class)->ask($chatId);
    }
}
