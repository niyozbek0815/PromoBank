<?php
namespace App\Telegram\Handlers\Routes;

use App\Telegram\Handlers\MainBack;
use App\Telegram\Handlers\Menu;
use App\Telegram\Handlers\Subscriptions;
use App\Telegram\Services\SubscriptionService;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Log;

class SubscriptionRouteHandler
{

    public function __construct(
        protected Translator $translator,
        protected SubscriptionService $subscriptionService
    ) {
    }
    public function handle($update, $notSubscribed, $isCheck = false)
    {

        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();


        if ($isCheck) {
            $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();
            return app(Subscriptions::class)->handle($chatId, $notSubscribed, $messageId);

        }
        if (!empty($notSubscribed)) {
            $this->subscriptionService->storePendingAction($chatId, $update);
            return app(Subscriptions::class)->handle($chatId, $notSubscribed, null);
        }
        return app(Menu::class)->handle($chatId);
    }
}
