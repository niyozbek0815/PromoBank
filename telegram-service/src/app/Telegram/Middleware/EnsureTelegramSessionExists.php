<?php

namespace App\Telegram\Middleware;

use App\Jobs\RegisterPrizeJob;
use App\Telegram\Handlers\Menu;
use App\Telegram\Handlers\Routes\AuthenticatedRouteHandler;
use App\Telegram\Handlers\Routes\RegisterRouteHandler;
use App\Telegram\Handlers\Routes\StartRouteHandler;
use App\Telegram\Handlers\Routes\SubscriptionRouteHandler;
use App\Telegram\Handlers\Routes\UpdateRouteHandler;
use App\Telegram\Handlers\Subscriptions;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\SessionStatusService;
use App\Telegram\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;
use Queue;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\CallbackQuery;
class EnsureTelegramSessionExists
{
    public function handle($update)
    {
        if ($update->getMyChatMember() || $update->getChatMember()) {
            return response()->noContent();
        }
        $message = $update->getMessage();
        $callback = $update->getCallbackQuery();

        $chatId = null;
        $messageText = null;
        $chatType = null;

        // Message obyektini tekshirish
        if ($message instanceof Message) {
            $chatId = $message->getChat()->getId();
            $messageText = $message->text ?? null;
            $chatType = $message->getChat()->getType();
        } elseif ($message instanceof \Illuminate\Support\Collection || is_array($message)) {
            $chatId = data_get($message, 'chat.id');
            $messageText = data_get($message, 'text');
            $chatType = data_get($message, 'chat.type');
        }

        // Callback query fallback
        if (!$chatId && $callback instanceof CallbackQuery) {
            $chatId = $callback->getMessage()?->getChat()?->getId();
            $messageText = $callback->getMessage()?->text ?? null;
            $chatType = $callback->getMessage()?->getChat()?->getType();
        }

        // Agar chat turini tekshirish (faqat private chatlarda davom etish)
        if ($chatType && $chatType !== 'private') {
            return response()->noContent();
        }

        if (!$chatId) {
            Log::warning("Telegram update chatId not found", [
                'update' => $update->toArray()
            ]);
            return response()->noContent();
        }

        $getData = $callback?->getData() ?? null;
        $isOpenRoute = $messageText && stripos($messageText, '/start') !== false;

        // Log::info("1EnsureTelegramSessionExists", ["chatId" => $chatId, "messageText" => $messageText, "getData" => $getData, "isOpenRoute" => $isOpenRoute]);
        if ($isOpenRoute) {
            return app(StartRouteHandler::class)->handle($update);
        }
        $status = app(SessionStatusService::class)->getStatus($chatId);

        //  Agar ro‘yxatdan o‘tish jarayonida bo‘lsa
        if ($status === 'in_register' && !$isOpenRoute) {
            return app(RegisterRouteHandler::class)->handle($update);
        }

        //  Agar ma’lumot yangilash jarayonida bo‘lsa
        if ($status === 'in_update' && !$isOpenRoute) {
            return app(UpdateRouteHandler::class)->handle($update);
        }

        //  Agar xabar "start"ni o‘z ichiga olsa


        //  Autentifikatsiyadan o‘tgan foydalanuvchilar
        if ($status === 'authenticated') {
            $notSubscribed = app(SubscriptionService::class)->check($chatId);
            if (!empty($notSubscribed) || in_array($getData, ['check_subscriptions', 'check_subscriptions_register'])) {
                app(SubscriptionRouteHandler::class)->handle($update, $chatId, $getData, $notSubscribed);
                return;
            }
            // Log::info("Middleware authenticated", [
            //     'chat_id' => $chatId,
            //     'notSubscribedCount' => count($notSubscribed),
            // ]);


            return app(AuthenticatedRouteHandler::class)->handle($update);
        }

        if ($status === 'none') {
            Log::info("Middleware none", ['chat_id' => $chatId]);
            $fakeUpdate = new \Telegram\Bot\Objects\Update([
                'update_id' => $update->getUpdateId(),
                'message' => [
                    'message_id' => $update->getMessage()?->getMessageId() ?? 0,
                    'from' => [
                        'id' => $chatId,
                        'is_bot' => false,
                        'first_name' => $update->getMessage()?->getFrom()?->getFirstName() ?? '',
                    ],
                    'chat' => [
                        'id' => $chatId,
                        'type' => 'private',
                    ],
                    'date' => now()->timestamp,
                    'text' => '/start',
                ]
            ]);

            return app(StartRouteHandler::class)->handle($fakeUpdate);
        }

        return response()->noContent();
    }
}
