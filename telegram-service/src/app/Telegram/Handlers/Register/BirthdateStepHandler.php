<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class BirthdateStepHandler
{
    public function __construct(protected Translator $translator)
    {
    }

    public function ask($chatId)
    {
        $this->sendMessage($chatId, $this->translator->get($chatId, 'ask_birthdate'), ['remove_keyboard' => true]);
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
            $this->sendMessage($chatId, $result);
            return;
        }

        app(RegisterService::class)->mergeToCache($chatId, [
            'birthdate' => $result,
            'state' => 'waiting_for_offer',
        ]);

        $this->sendMessage($chatId, $this->translator->get($chatId, 'birthdate_received'));
        return app(OfertaStepHandler::class)->ask($chatId);
    }

    public function handleUpdate(Update $update)
    {
        $message = $update->getMessage();
        $chatId = $message?->getChat()?->getId();
        $text = $message?->getText();

        [$valid, $result] = $this->validateAndNormalizeBirthdate($text, $chatId);

        if (!$valid) {
            $this->sendMessage($chatId, $result);
            return;
        }

        $this->sendMessage($chatId, $this->translator->get($chatId, 'birthdate_received'));
        app(UserUpdateService::class)->mergeToCache($chatId, [
            'birthdate' => $result,
            'state' => 'complete',
        ]);
        app(UserUpdateService::class)->finalizeUserRegistration($update);
        $replyMarkup = [
            'keyboard' => [
                [['text' => $this->translator->get($chatId, 'open_main_menu')]],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
        $this->sendMessage($chatId, $this->translator->get($chatId, 'profile_update_success'), $replyMarkup);
    }

    protected function sendMessage($chatId, $text, $replyMarkup = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
        ];
        if ($replyMarkup) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }
        Telegram::sendMessage($params);
    }
}
