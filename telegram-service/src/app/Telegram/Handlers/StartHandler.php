<?php

namespace App\Telegram\Handlers;

use Telegram\Bot\Laravel\Facades\Telegram;

class StartHandler
{
    public function handle(int $chatId)
    {
        $this->sendLanguageSelection($chatId);
        return response()->noContent();
    }

    protected function sendLanguageSelection(int $chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "Iltimos, tilni tanlang:\nПожалуйста, выберите язык:\nИлтимос, тилни танланг:",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "🇺🇿 O‘zbekcha", 'callback_data' => 'lang_uz'],
                        ['text' => "🇷🇺 Русский", 'callback_data' => 'lang_ru'],
                        ['text' => "🇺🇿 Кирилл", 'callback_data' => 'lang_kr'],
                    ]
                ]
            ])
        ]);
    }
}
