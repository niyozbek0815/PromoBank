<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;

class GenderStepHandler
{

    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }

    public function ask($chatId)
    {
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'ask_gender'),
            'reply_markup' => $this->getGenderKeyboard($chatId),
        ]);
        return;
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
            $this->sender->handle([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'invalid_gender_format'),
            ]);
            return;
        }
        $this->sender->handle([
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
