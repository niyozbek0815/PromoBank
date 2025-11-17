<?php
namespace App\Telegram\Handlers;

use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;

class Menu
{
    public function __construct(protected Translator $translator)
    {
        // Constructor can be used for dependency injection if needed
    }
    public function handle($chatId)
    {
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'main_menu_title'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text'    => $this->translator->get($chatId, 'menu_promotions'),
                            'web_app' => ['url' => 'https://qadarun.com/webapp/promotions/7'],
                        ],
                        // [
                        //     'text'    => $this->translator->get($chatId, key: 'menu_games'),
                        //     'web_app' => ['url' => 'https://qadarun.com/webapp/games'],
                        // ],
                    ],
                    [
                        // ['text' => $this->translator->get($chatId, 'menu_news'), 'callback_data' => 'menu_news'],
                        ['text' => $this->translator->get($chatId, 'menu_social'), 'callback_data' => 'menu_social'],
                    ],
                    [
                                                ['text' => $this->translator->get($chatId, 'menu_referral'), 'callback_data' => 'menu_referral'],
                    ],
                    [
                        ['text' => $this->translator->get($chatId, 'menu_profile'), 'callback_data' => 'menu_profile'],
                    ],
                ],
            ]),
        ]);
    }
}
