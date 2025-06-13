<?php

namespace App\Telegram\Handlers;

use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class CallbackHandler
{
    public function handle($callback)
    {
        $chatId = $callback->getMessage()->getChat()->getId();
        $data = $callback->getData();

        if (str_starts_with($data, 'lang_')) {
            if (str_starts_with($data, 'lang_')) {
                return app(LangCallbackHandler::class)->handle($callback);
            }
        }

        return response()->noContent();
    }
}
