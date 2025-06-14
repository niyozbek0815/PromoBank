<?php
namespace App\Telegram\Handlers\Register;

use Telegram\Bot\Laravel\Facades\Telegram;

class SendPhoneRequest
{
    public function handle($chatId)
    {
        $text   = app(\App\Telegram\Services\Translator::class)->get($chatId, 'ask_phone');
        $button = app(\App\Telegram\Services\Translator::class)->get($chatId, 'share_phone_button');

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $text,
            'reply_markup' => json_encode([
                'keyboard'          => [
                    [['text' => $button, 'request_contact' => true]],
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }
}
