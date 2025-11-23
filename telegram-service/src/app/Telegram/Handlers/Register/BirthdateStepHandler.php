<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Telegram\Bot\Objects\Update;

class BirthdateStepHandler
{
    public function __construct(protected Translator $translator, protected SendMessages $sender)
    {
    }

    public function ask($chatId)
    {
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'ask_birthdate'),
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);
    }

    protected function validateAndNormalizeBirthdate($text, $chatId)
    {
        if (preg_match('/^(\d{1,2})[.,\s\/\\-]?(\d{1,2})[.,\s\/\\-]?(\d{4})$/', trim($text), $matches)) {
            [$day, $month, $year] = [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
            $minYear = 1900;
            $maxYear = (int) now()->subYears(5)->format('Y');
            if (!checkdate($month, $day, $year) || $year < $minYear || $year > $maxYear) {
                return [false, $this->translator->get($chatId, 'invalid_birthdate_format')];
            }
            return [true, sprintf('%04d-%02d-%02d', $year, $month, $day)];
        }
        return [false, $this->translator->get($chatId, 'invalid_birthdate_format')];
    }

    public function handle(Update $update)
    {
        $message = $update->getMessage();
        $chatId = $message?->getChat()?->getId();
        $text = $message?->getText();

        [$valid, $result] = $this->validateAndNormalizeBirthdate($text, $chatId);

        if (!$valid) {
            $this->sender->handle([
                'chat_id' => $chatId,
                'text' => $result,
            ]);
            return;
        }

        app(RegisterService::class)->mergeToCache($chatId, [
            'birthdate' => $result,
            'state' => 'waiting_for_offer',
        ]);
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'birthdate_received'),
        ]);
        return app(OfertaStepHandler::class)->ask($chatId);
    }

    public function handleUpdate(Update $update)
    {
        $message = $update->getMessage();
        $chatId = $message?->getChat()?->getId();
        $text = $message?->getText();

        [$valid, $result] = $this->validateAndNormalizeBirthdate($text, $chatId);

        if (!$valid) {
            $this->sender->handle([
                'chat_id' => $chatId,
                'text' => $result,
            ]);
            return;
        }
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'birthdate_received'),
        ]);
        app(UserUpdateService::class)->mergeToCache($chatId, [
            'birthdate' => $result,
            'state' => 'complete',
        ]);
        $response = app(UserUpdateService::class)->finalizeUserRegistration($update);
        $replyMarkup = [
            'keyboard' => [
                [['text' => $this->translator->get($chatId, 'open_main_menu')]],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
        if ($response == true) {
            $this->sender->handle([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'profile_update_success'),
                'reply_markup' => json_encode($replyMarkup),
            ]);
            return;
        }
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'error_retry_later'),
            'reply_markup' => json_encode($replyMarkup),
        ]);
        return;
    }
}
