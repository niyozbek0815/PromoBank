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
        $message = $update->getMessage()?->getText();
        $getData = $update->getCallbackQuery()?->getData();
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        Log::info("SubscriptionRouteHandler data =>", [
            'message' => $message,
            'getData' => $getData,
            'chat_id' => $chatId,
            'notSubscribedCount' => count($notSubscribed),
        ]);

        // --- 1) Agar user "✅ Tekshirish" tugmasini bosgan bo'lsa, CALLBACK ishlaymiz (PRIORITET)
        if ($isCheck) {
            $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();
            Log::info("User still not subscribed, updating message", [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'notSubscribed' => $notSubscribed,
            ]);

            // Yangilash uchun messageId bo'lishi shart — agar yo'q bo'lsa, yangi yubor
            return app(Subscriptions::class)->handle($chatId, $notSubscribed, $messageId);

        }

        // --- 2) Agar callback emas va notSubscribed mavjud bo'lsa — yangi xabar yuboramiz
        if (!empty($notSubscribed)) {
            $this->subscriptionService->storePendingAction($chatId, $update);
            return app(Subscriptions::class)->handle($chatId, $notSubscribed, null);
        }

        // --- 3) Hech qanday subscription muammosi yo'q — davom et
        return app(Menu::class)->handle($chatId);

        // return app(AuthenticatedRouteHandler::class)->handle($update);
    }
}
