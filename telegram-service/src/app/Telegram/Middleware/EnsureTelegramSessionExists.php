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
use Illuminate\Support\Facades\Cache;

class EnsureTelegramSessionExists
{
    public function handle($update)
    {
        $messageText = $update->getMessage()?->getText();
        $chatId      = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $getData = $update->getCallbackQuery()?->getData();

        $isOpenRoute = $messageText === '/start';

        $status = app(RegisterService::class)->getSessionStatus($chatId);
        // Log::info("Middlewarega kirish: status-> " . $status . ". Message: " . $messageText);
        if ($status == 'in_register' && ! $isOpenRoute) {
            // Log::info("in_register");
            return app(RegisterRouteHandler::class)->handle($update);
        }
        if ($status == 'in_update' && ! $isOpenRoute) {
            // Log::info("in_update");
            app(UpdateRouteHandler::class)->handle($update);
        }

        if ($isOpenRoute) {
            // Log::info("Middlewarega openRoute");
            return app(StartRouteHandler::class)->handle($update);
        }
        Cache::store('redis')->forget("tg_subscriptions_ok:$chatId");

        if ($status == 'authenticated') {
            $notSubscribed = app(SubscriptionService::class)->checkUserSubscriptions($chatId);
            Log::info("Middleware authenticated", [
                'chat_id' => $chatId,
                'notSubscribedCount' => count($notSubscribed),
            ]);
            if ($getData === 'check_subscriptions') {
                if (empty($notSubscribed)) {
                    $pending = app(SubscriptionService::class)->getPendingAction($chatId);
                    $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

                    // Eski xabarni o‘chirish
                    app(SubscriptionService::class)->deleteMessage($chatId, $messageId);
                    // Pending action mavjud bo‘lsa uni Update obyektiga o‘giramiz
                    if ($pending) {
                        $updateObject = new \Telegram\Bot\Objects\Update($pending);
                        return app(\App\Telegram\Handlers\Routes\AuthenticatedRouteHandler::class)->handle($updateObject);
                    }

                    // Agar pending bo‘lmasa — hech narsa qilmaslik
                    return response()->noContent();
                } else {
                    return app(SubscriptionRouteHandler::class)->handle($update, $notSubscribed, true);
                }
            }
            if (!empty($notSubscribed)) {
                return app(SubscriptionRouteHandler::class)->handle($update, $notSubscribed);
            }

            return app(AuthenticatedRouteHandler::class)->handle($update);
        }
        if ($status == 'none') {
            Log::info("Middleware none");
        }

        return response()->noContent();

    }
}
