<?php
namespace App\Telegram\Handlers;

use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class MainBack
{
    public function __construct(protected Translator $translator)
    {
        // Constructor can be used for dependency injection if needed
    }
    public function handle(Update $update)
    {
        $chatId    = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

        // Edit the message text to show the main menu
        Telegram::editMessageText([
            'chat_id'      => $chatId,
            'message_id'   => $messageId, // You need to pass actual message_id from the callback
            'text'         => $this->translator->get($chatId, 'main_menu_title'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text'    => $this->translator->get($chatId, 'menu_promotions'),
                            'web_app' => ['url' => 'https://qadarun.com/'],
                        ],
                        [
                            'text'    => $this->translator->get($chatId, 'menu_games'),
                            'web_app' => ['url' => 'https://qadarun.com'],
                        ],
                    ],
                    [
                        ['text' => $this->translator->get($chatId, 'menu_news'), 'callback_data' => 'menu_news'],
                        ['text' => $this->translator->get($chatId, 'menu_social'), 'callback_data' => 'menu_social'],
                    ],
                    [
                        ['text' => $this->translator->get($chatId, 'menu_profile'), 'callback_data' => 'menu_profile'],
                    ],
                ],
            ]),
        ]);
    }

}