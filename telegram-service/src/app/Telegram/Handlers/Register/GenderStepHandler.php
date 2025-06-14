<?php

namespace App\Telegram\Handlers\Register;

use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class GenderStepHandler
{
    public function ask($chatId)
    {
        Cache::store('redis')->put("tg_reg_state:$chatId", 'waiting_for_gender', now()->addDays(7));

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "👫 Iltimos, jinsingizni tanlang",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Erkak'], ['text' => 'Ayol']]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ])
        ]);
    }

    public function handle($chatId, $gender)
    {
        $genderCode = strtolower($gender) === 'erkak' ? 'M' : (strtolower($gender) === 'ayol' ? 'F' : 'U');
        Cache::store('redis')->put("tg_reg_data:$chatId:gender", $genderCode);

        return app(CompleteRegistrationHandler::class)->handle($chatId);
    }
}
