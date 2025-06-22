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
        $text    = trim($message?->getText());

        // 1 yoki 2 xonali kun va oyga moslash
        if (preg_match('/^(\d{1,2})[.,\s\/\\-]?(\d{1,2})[.,\s\/\\-]?(\d{4})$/', $text, $matches)) {
            $day   = (int) $matches[1];
            $month = (int) $matches[2];
            $year  = (int) $matches[3];

            // Sanani tekshirish: real kun/oymi
            if (! checkdate($month, $day, $year)) {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text'    => $this->translator->get($chatId, 'invalid_birthdate_format'),
                ]);
                return;
            }

            // Yil chegarasi: 1900-yildan katta va oxirgi 5 yildan oldingi bo'lishi kerak
            $minYear = 1900;
            $maxYear = (int) now()->subYears(5)->format('Y');

            if ($year < $minYear || $year > $maxYear) {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text'    => $this->translator->get($chatId, 'invalid_birthdate_format'),
                ]);
                return;
            }

            // Formatlash: YYYY-MM-DD
            $normalized = sprintf('%04d-%02d-%02d', $year, $month, $day);

            app(RegisterService::class)->mergeToCache($chatId, [
                'birthdate' => $normalized,
                'state'     => 'waiting_for_offer',
            ]);

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'birthdate_received'),
            ]);

            return app(OfertaStepHandler::class)->ask($chatId);

            // Optionally finalize registration
        } else {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'invalid_birthdate_format'),
            ]);
        }
    }
}
