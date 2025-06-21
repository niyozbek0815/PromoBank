<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class BirthdateStepHandler
{
    public function __construct(protected Translator $translator)
    {
    }
    public function ask($chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => $this->translator->get($chatId, 'ask_birthdate'), 'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);
    }

    public function handle(Update $update)
    {
        $message = $update->getMessage();
        $chatId  = $message?->getChat()?->getId();
        $text    = $message?->getText();

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'invalid_birthdate_format')]);
            return;
        }

        app(RegisterService::class)->mergeToCache($chatId, [
            'birthdate' => $text,
            'state'     => 'complete',
        ]);

        // Optionally send confirmation

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => $this->translator->get($chatId, 'birthdate_received'),
            // 'text'    => "✅ Ro'yxatdan muvaffaqiyatli o'tdingiz!",
        ]);
        app(abstract :RegisterService::class)->finalizeUserRegistration($update);

    }

    // Va oxirgi comlete funksiyani yozishim va user yaratishim kerak.

}