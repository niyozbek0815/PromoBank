<?php
namespace App\Telegram\Handlers;

use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;

class Welcome
{
    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }
    public function handle($chatId)
    {
        return $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'welcome'),
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => $this->translator->get($chatId, 'open_main_menu')]],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
}
