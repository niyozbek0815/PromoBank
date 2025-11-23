<?php

namespace App\Telegram\Handlers\Routes;

use App\Jobs\StartAndRefferralJob;
use App\Telegram\Handlers\Menu;
use App\Telegram\Handlers\Start\StartHandler;
use App\Telegram\Handlers\Subscriptions;
use App\Telegram\Handlers\Welcome;
use App\Telegram\Services\StartService;
use App\Telegram\Services\SubscriptionService;
use App\Telegram\Services\UserSessionService;
use Illuminate\Support\Facades\Cache;
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
        $username = $chat?->get('first_name')
            ?? $chat?->first_name
            ?? $chat?->get('username')
            ?? $chat?->username
            ?? $from?->get('username')
            ?? $from?->username
            ?? $from?->get('first_name')
            ?? $from?->first_name
            ?? null;
        $referrerId = null;

        if (preg_match('/^\/start\s+(\d+)/', trim($messageText ?? ''), $m)) {
            $referrerId = ($m[1] == $chatId) ? null : (string) $m[1];
        }
        Cache::store('bot')->forget("tg_user_data:$chatId");
        Cache::store('bot')->forget('tg_user:' . $chatId);
        Cache::store('bot')->forget('tg_user_update:' . $chatId);
        if (app(StartService::class)->handle($chatId, $username, $referrerId)) {
            app(Welcome::class)->handle($chatId);
            $notSubscribed = app(SubscriptionService::class)->check($chatId);
            if (!empty($notSubscribed)) {
                return app(Subscriptions::class)->showSubscriptionPrompt(
                    $chatId,
                    $notSubscribed,
                    null,
                    'check_subscriptions_register'
                );
            }
            return app(Menu::class)->handle($chatId);
        }

        return app(StartHandler::class)->handle($chatId);
    }
}
