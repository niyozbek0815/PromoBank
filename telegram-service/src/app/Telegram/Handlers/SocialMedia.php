<?php
namespace App\Telegram\Handlers;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class SocialMedia
{
    public function handle(Update $update)
    {
        $chatId    = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();
        Telegram::editMessageText([
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'text'         => "📱 Bizning ijtimoiy tarmoqlarimiz azo bo'ling va bizni kuzating:",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🌐 Veb sayt',
                            'url'  => 'https://example.com',
                        ],
                        [
                            'text' => '📲 Mobil ilova',
                            'url'  => 'https://play.google.com/store/apps/details?id=com.example.app',
                        ],
                    ],
                    [
                        [
                            'text' => '📸 Instagram',
                            'url'  => 'https://instagram.com/example',
                        ],
                        [
                            'text' => '📘 Facebook',
                            'url'  => 'https://facebook.com/example',
                        ],
                    ],
                    [
                        [
                            'text' => '▶️ YouTube',
                            'url'  => 'https://youtube.com/@example',
                        ],
                        [
                            'text' => '📢 Telegram kanal',
                            'url'  => 'https://t.me/example_channel',
                        ],
                    ],
                    [
                        [
                            'text'          => "Ortga",
                            'callback_data' => 'back_to_main_menu',
                        ],
                    ],
                ],
            ]),
        ]);

    }
}
