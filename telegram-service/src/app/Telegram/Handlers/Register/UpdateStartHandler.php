<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;

class UpdateStartHandler
{
    public function __construct(protected Translator $translator, protected SendMessages $sender)
    {
        // Constructor can be used for dependency injection if needed
    }
    public function handle($chatId)
    {

        app(UserUpdateService::class)->mergeToCache($chatId, ['state' => 'waiting_for_language']);
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'profile_update_welcome'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);
        return app(LanguageHandler::class)->ask($chatId);
    }
}