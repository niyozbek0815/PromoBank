<?php

namespace App\Telegram\Middleware;

use App\Telegram\Handlers\Routes\AuthenticatedRouteHandler;
use App\Telegram\Handlers\Routes\RegisterRouteHandler;
use App\Telegram\Handlers\Routes\StartRouteHandler;
use App\Telegram\Handlers\Routes\SubscriptionRouteHandler;
use App\Telegram\Handlers\Routes\UpdateRouteHandler;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;

class EnsureTelegramSessionExists
{
    public function handle($update)
    {
        $message = $update->getMessage()->first(); // agar collection bo'lsa
        $message = $update->getMessage();
        $callback = $update->getCallbackQuery();

        // Xabarning textini xavfsiz olish
        $messageText = $message?->getText() ?? null;

        // Chat ID ni xavfsiz olish
        $chatId =
            $message?->getChat()?->getId()
            ?? $callback?->getMessage()?->getChat()?->getId()
            ?? null;

        $getData = $callback?->getData() ?? null;
Log::info("EnsureTelegramSessionExists chatId: $chatId, messageText: $messageText, getData: $getData");
        // 🔹 "start" so‘zi mavjudligini tekshirish (katta-kichik harf farq qilmaydi)
        $isOpenRoute = $messageText && stripos($messageText, '/start') !== false;
        if ($isOpenRoute) {
            Log::info("Middleware openRoute", [
                'chat_id' => $chatId,
                'text' => $messageText,
            ]);

            return app(StartRouteHandler::class)->handle($update);
        }
        $status = app(RegisterService::class)->getSessionStatus($chatId);

        // 🔹 Agar ro‘yxatdan o‘tish jarayonida bo‘lsa
        if ($status === 'in_register' && !$isOpenRoute) {
            return app(RegisterRouteHandler::class)->handle($update);
        }

        // 🔹 Agar ma’lumot yangilash jarayonida bo‘lsa
        if ($status === 'in_update' && !$isOpenRoute) {
            return app(UpdateRouteHandler::class)->handle($update);
        }

        // 🔹 Agar xabar "start"ni o‘z ichiga olsa


        // 🔹 Autentifikatsiyadan o‘tgan foydalanuvchilar
        if ($status === 'authenticated') {
            $notSubscribed = app(SubscriptionService::class)->checkUserSubscriptions($chatId);

            Log::info("Middleware authenticated", [
                'chat_id' => $chatId,
                'notSubscribedCount' => count($notSubscribed),
            ]);

            if ($getData === 'check_subscriptions') {
                if (empty($notSubscribed)) {
                    $pending = app(SubscriptionService::class)->getPendingAction($chatId);
                    $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

                    app(SubscriptionService::class)->deleteMessage($chatId, $messageId);

                    if ($pending) {
                        $updateObject = new \Telegram\Bot\Objects\Update($pending);
                        return app(AuthenticatedRouteHandler::class)->handle($updateObject);
                    }

                    return response()->noContent();
                }

                return app(SubscriptionRouteHandler::class)->handle($update, $notSubscribed, true);
            }

            if (!empty($notSubscribed)) {
                return app(SubscriptionRouteHandler::class)->handle($update, $notSubscribed);
            }

            return app(AuthenticatedRouteHandler::class)->handle($update);
        }

        if ($status === 'none') {
            Log::info("Middleware none", ['chat_id' => $chatId]);
        }

        return response()->noContent();
    }
}
