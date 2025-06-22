<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;

class NameStepHandler
{
    public function __construct(
        protected Translator $translator
    ) {
        // Constructor can be used for dependency injection if needed
    }

    public function ask($chatId)
    {
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'ask_name'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);
    }

    public function handle($update)
    {
        $messageText = $update->getMessage()?->getText();
        $chatId      = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        if (! preg_match('/^[\p{L}\s]{3,}$/u', $messageText)) {
            return Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'invalid_name_format'),
            ]);
        }
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => "✅ Ismingiz qabul qilindi.",
        ]);
        app(RegisterService::class)->mergeToCache($chatId, [
            'chat_id' => $chatId,
            'name'    => $messageText,
            'state'   => 'waiting_for_phone',
        ]);
        return app(PhoneStepHandler::class)->ask($chatId);
    }
}
