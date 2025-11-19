<?php
namespace App\Telegram\Handlers\Register;

use App\Jobs\RegisterPrizeJob;
use App\Telegram\Handlers\Menu;
use App\Telegram\Handlers\Routes\SubscriptionRouteHandler;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SubscriptionService;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Queue;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class OfertaStepHandler
{
    public function __construct(protected Translator $translator)
    {
    }

    public function ask($chatId)
    {
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'ask_offer'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => $this->translator->get($chatId, 'offer_button'),
                            'url'  => 'https://docs.google.com/document/d/1kUNYpFJ6lC-yeNw1CTaIzaHhygeNegoD/edit',
                        ],
                    ],
                    [
                        [
                            'text'          => $this->translator->get($chatId, 'confirm'),
                            'callback_data' => 'next:offer',
                        ],
                    ],
                ],
            ]),
        ]);
    }

    public function handle(Update $update)
    {
        $callbackQuery = $update->getCallbackQuery();
        $message       = $callbackQuery?->getMessage();
        $chatId        = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId     = $message?->getMessageId();
        $data          = $callbackQuery?->getData();

        if ($callbackQuery && $data === 'next:offer') {
            if ($msgId = $callbackQuery->getMessage()?->getMessageId()) {
                Telegram::deleteMessage([
                    'chat_id'    => $chatId,
                    'message_id' => $msgId,
                ]);
            }
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'offer_received'),
            ]);
            app(RegisterService::class)->mergeToCache($chatId, [
                'offer' => true,
                'state' => 'complete',
            ]);
            app(RegisterService::class)->finalizeUserRegistration($update);
              $notSubscribed = app(SubscriptionService::class)->checkUserSubscriptions($chatId);
            if (!empty($notSubscribed)) {
                return app(SubscriptionRouteHandler::class)->handle($update, $notSubscribed);
            }
                return app(Menu::class)->handle($chatId);
        }
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => $this->translator->get($chatId, 'invalid_offer_format'),
        ]);

    }
}
