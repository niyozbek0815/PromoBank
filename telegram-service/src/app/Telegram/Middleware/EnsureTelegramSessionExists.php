<?php

namespace App\Telegram\Middleware;

use App\Telegram\Handlers\Menu;
use App\Telegram\Handlers\Routes\AuthenticatedRouteHandler;
use App\Telegram\Handlers\Routes\RegisterRouteHandler;
use App\Telegram\Handlers\Routes\StartRouteHandler;
use App\Telegram\Handlers\Routes\SubscriptionRouteHandler;
use App\Telegram\Handlers\Routes\UpdateRouteHandler;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SessionStatusService;
use App\Telegram\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;
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

        Log::info("1EnsureTelegramSessionExists", ["chatId" => $chatId, "messageText" => $messageText, "getData" => $getData, "isOpenRoute" => $isOpenRoute]);
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
                        return app(Menu::class)->handle($chatId);
                        // return app(AuthenticatedRouteHandler::class)->handle($updateObject);
                    }

                    return app(Menu::class)->handle($chatId);
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

            // Agar foydalanuvchi hali sessionga ega emas — start xabarini simulyatsiya qilamiz
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

            // /start handler ga yo‘naltiramiz
            return app(StartRouteHandler::class)->handle($fakeUpdate);
        }

        return response()->noContent();
    }
}
