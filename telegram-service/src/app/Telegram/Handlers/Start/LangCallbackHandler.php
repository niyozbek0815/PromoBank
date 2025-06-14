<?php
namespace App\Telegram\Handlers\Start;

use App\Telegram\Handlers\Register\SendPhoneRequest;
use App\Telegram\Handlers\Welcome;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class LangCallbackHandler
{
    public function handle($callback)
    {
        $chatId = $callback->getMessage()->getChat()->getId();
        $lang   = str_replace('lang_', '', $callback->getData());

        Cache::store('redis')->put("tg_lang:$chatId", $lang, now()->addDays(7));

        Telegram::answerCallbackQuery([
            'callback_query_id' => $callback->getId(),
        ]);

        Telegram::deleteMessage([
            'chat_id'    => $chatId,
            'message_id' => $callback->getMessage()->getMessageId(),
        ]);

        if (! app(\App\Telegram\Services\UserSessionService::class)->exists($chatId)) {
            return app(SendPhoneRequest::class)->handle($chatId);
        } else {
            return app(Welcome::class)->handle($chatId);
        }
    }
}
