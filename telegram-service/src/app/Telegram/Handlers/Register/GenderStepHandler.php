<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
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
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'ask_gender'),
            'reply_markup' => json_encode([
                'keyboard'          => [
                    [['text' => $this->translator->get($chatId, 'gender_male')], ['text' => $this->translator->get($chatId, 'gender_female')]],
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    public function handle(string $chatId, string $text)
    {
        $genderMap = [
            $this->translator->get($chatId, 'gender_male')   => 'male',
            $this->translator->get($chatId, 'gender_female') => 'female',
        ];

        if (! isset($genderMap[$text])) {
            return Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'invalid_gender'),
            ]);
        }
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'gender_received'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);

        $gender = $genderMap[$text];

        // Redisga yozish
        app(RegisterService::class)->mergeToCache($chatId, [
            'gender' => $gender,
            'state'  => 'waiting_for_region',
        ]);

        return app(RegionStepHandler::class)->ask($chatId); // keyingi step
    }
}