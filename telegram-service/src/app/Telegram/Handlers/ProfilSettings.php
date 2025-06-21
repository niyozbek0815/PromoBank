<?php
namespace App\Telegram\Handlers;

use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class ProfilSettings
{
    public function __construct(protected Translator $translator)
    {
        // Constructor can be used for dependency injection if needed
    }
    public function handle(Update $update)
    {
        $chatId    = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

        Telegram::editMessageText([
            'chat_id'      => $chatId,
            'message_id'   => $messageId, // You need to pass actual message_id from the callback
            'text'         => "Proofile sozlamalari",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "Malumotlarni taxrirlash", 'callback_data' => 'edit_profile'],
                    ], [
                        ['text' => "Tilni o'zgartirish", 'callback_data' => 'change_language'],
                    ],
                    [
                        ['text' => "Ortga", 'callback_data' => 'back_to_main_menu'],
                    ],
                ],
            ]),
        ]);

    }
}