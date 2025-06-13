<?php

namespace App\Telegram\Handlers;

use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;

class Welcome
{
    public function handle($chatId)
    {
        $translator = app(Translator::class);
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $translator->get($chatId, 'welcome'),
        ]);
    }
}
