<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Handlers\Menu;
use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;

class AlreadyRegisterStepHandler
{
    public function __construct(
        protected Translator $translator,
    ) {}
    public function handle($chatId)
    {
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'already_registered'),
            'reply_markup' => json_encode([
                'keyboard'          => [
                    [['text' => $this->translator->get($chatId, 'open_main_menu')]],
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => false,
            ]),
        ]);

        // app(Menu::class)->handle($chatId);
    }

}
