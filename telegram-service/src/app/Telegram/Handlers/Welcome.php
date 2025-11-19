<?php
namespace App\Telegram\Handlers;

use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;

class Welcome
{
    public function handle($chatId)
    {
        $translator = app(Translator::class);
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $translator->get($chatId, 'welcome'),
            'reply_markup' => json_encode([
                'keyboard'          => [
                    [['text' => $translator->get($chatId, 'open_main_menu')]],
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => false,
            ]),
        ]);
        // app(Menu::class)->handle($chatId);
    }
}
