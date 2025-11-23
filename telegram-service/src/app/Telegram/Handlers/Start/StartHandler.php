<?php
namespace App\Telegram\Handlers\Start;

use App\Telegram\Handlers\Register\LanguageHandler;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;

class StartHandler
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
            'text' => $this->translator->get($chatId, 'start'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);
        app(RegisterService::class)->mergeToCache($chatId, [
            'chat_id' => $chatId,
            'state' => 'waiting_for_language',
        ]);
        return app(LanguageHandler::class)->ask($chatId);
    }

}