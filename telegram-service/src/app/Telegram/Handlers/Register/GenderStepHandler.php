<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Telegram\Bot\Laravel\Facades\Telegram;

class GenderStepHandler
{
    protected Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function ask($chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'ask_gender'),
            'reply_markup' => $this->getGenderKeyboard($chatId),
        ]);
    }

    protected function getGenderKeyboard($chatId)
    {
        return json_encode([
            'keyboard' => [
                [
                    ['text' => $this->translator->get($chatId, 'gender_male')],
                    ['text' => $this->translator->get($chatId, 'gender_female')],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);
    }

    protected function getGenderMap($chatId)
    {
        return [
            $this->translator->get($chatId, 'gender_male') => 'male',
            $this->translator->get($chatId, 'gender_female') => 'female',
        ];
    }

    protected function handleGender(string $chatId, string $text, $service)
    {
        $genderMap = $this->getGenderMap($chatId);

        if (!isset($genderMap[$text])) {
            return Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'invalid_gender_format'),
            ]);
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'gender_received'),
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);


        app($service)->mergeToCache($chatId, [
            'gender' => $genderMap[$text],
            'state' => 'waiting_for_region',
        ]);

        return app(RegionStepHandler::class)->ask($chatId);
    }

    public function handle(string $chatId, string $text)
    {
        return $this->handleGender($chatId, $text, RegisterService::class);
    }

    public function handleUpdate(string $chatId, string $text)
    {
        return $this->handleGender($chatId, $text, UserUpdateService::class);
    }
}
