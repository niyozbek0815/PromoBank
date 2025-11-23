<?php
namespace App\Telegram\Handlers\Routes;

use App\Jobs\RegisterPrizeJob;
use App\Telegram\Handlers\Menu;
use App\Telegram\Handlers\Subscriptions;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\SubscriptionService;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Queue;

class SubscriptionRouteHandler
{

    public function __construct(
        protected Translator $translator,
        protected SubscriptionService $subscriptionService
    ) {
    }
    public function handle($update, int $chatId, ?string $callbackData = null, $notSubscribed = [])
    {
        $callback = $update->getCallbackQuery();
        $messageId = $callback?->getMessage()?->getMessageId();
        if ($callback) {
            app(SendMessages::class)->answerCallback([
                'callback_query_id' => $callback->getId(),
                'text' => '',
                'show_alert' => false,
            ]);
        }


        switch ($callbackData) {

            case 'check_subscriptions':
                if (!empty($notSubscribed)) {
                    return app(Subscriptions::class)->showSubscriptionPrompt($chatId, $notSubscribed, $messageId);
                }
                app(SendMessages::class)->delete([
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]);
                return app(Menu::class)->handle($chatId);

            case 'check_subscriptions_register':
                if (!empty($notSubscribed)) {
                    return app(Subscriptions::class)->showSubscriptionPrompt(
                        $chatId,
                        $notSubscribed,
                        $messageId,
                        'check_subscriptions_register'
                    );
                }
                app(SendMessages::class)->delete([
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]);

                Queue::connection('rabbitmq')
                    ->push(new RegisterPrizeJob($chatId));

                return app(Menu::class)->handle($chatId);
        }
        if (!empty($notSubscribed)) {
            return app(Subscriptions::class)->showSubscriptionPrompt($chatId, $notSubscribed, $messageId);
        }
    }
}