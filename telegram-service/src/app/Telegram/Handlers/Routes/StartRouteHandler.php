<?php

namespace App\Telegram\Handlers\Routes;

use App\Jobs\RefferralJob;
use App\Jobs\StartAndRefferralJob;
use App\Telegram\Handlers\Start\StartHandler;
use App\Telegram\Handlers\Start\ReferralStartHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Telegram\Bot\Objects\Update;

class StartRouteHandler
{

    public function handle(Update $update)
    {
        $message = $update->getMessage();
        $callback = $update->getCallbackQuery();

        $messageText = $message?->getText() ?? $callback?->getData();

        // 🔹 Foydalanuvchi va chat obyektlarini olish
        $from = $message?->getFrom() ?? $callback?->getFrom();
        $chat = $message?->getChat() ?? $callback?->getMessage()?->getChat();

        $chatId = $chat?->getId();

        // 🔹 Username olishda eng ishonchli usul
        $username =   $chat?->get('first_name')
            ?? $chat?->first_name
            ?? $chat?->get('username')
            ?? $chat?->username
            ??  $from?->get('username')
            ?? $from?->username
            ?? $from?->get('first_name')
            ?? $from?->first_name
            ?? null;
        $referrerId = null;

        if (preg_match('/^\/start\s+(\d+)/', trim($messageText ?? ''), $matches)) {
            $referrerId = (string) $matches[1];
        }

        Log::info("StartRouteHandler dispatch StartAndRefferralJob", [
            'chat_id' => $chatId,
            'referrer_id' => $referrerId,
            'username' => $username,
            'message_text' => $messageText,
            'update'=>$update
        ]);

        Cache::store('redis')->forget("tg_user_data:$chatId");
        Cache::store('redis')->forget("tg_user:$chatId");

        Queue::connection('rabbitmq')->push(new StartAndRefferralJob(
            $chatId,
            $username,
            $referrerId
        ));

        return app(StartHandler::class)->startAsk($chatId);
    }
}




