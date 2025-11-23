<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;

class AlreadyRegisterStepHandler
{
    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender

    ) {
    }
    public function handle($chatId)
    {
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'already_registered'),
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => $this->translator->get($chatId, 'open_main_menu')]],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
        return;
    }

}
