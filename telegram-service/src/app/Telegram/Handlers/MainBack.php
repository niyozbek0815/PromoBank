<?php
namespace App\Telegram\Handlers;

use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use Telegram\Bot\Objects\Update;

class MainBack
{
    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }

    public function handle(Update $update)
    {
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();

        $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

        $text = $this->translator->get($chatId, 'main_menu_title');

        $replyMarkup = json_encode([
            'inline_keyboard' => [
                [
                    [
                        'text' => $this->translator->get($chatId, 'menu_promotions'),
                        'web_app' => ['url' => 'https://promobank.io/webapp/promotions/1'],
                    ],
                ],
                [
                    ['text' => $this->translator->get($chatId, 'menu_referral'), 'callback_data' => 'menu_referral'],
                ],
                [
                    ['text' => $this->translator->get($chatId, 'menu_social'), 'callback_data' => 'menu_social'],
                ],

                [
                    ['text' => $this->translator->get($chatId, 'menu_profile'), 'callback_data' => 'menu_profile'],
                ],
            ],
        ]);

        $this->sender->editMessage([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => $replyMarkup,
        ]);
    }
}
