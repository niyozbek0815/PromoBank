<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegionsAndDistrictService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Telegram\Bot\Objects\Update;

class DistrictStepHandler
{
    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }

    public function ask($chatId, $region_id)
    {
        $districts = app(RegionsAndDistrictService::class)->district($region_id);
        if (empty($districts)) {
            $this->sender->handle([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'region_list_failed'),
            ]);
            return;
        }

        $keyboard = array_map(
            fn($id, $name) => [
                [
                    'text' => $name,
                    'callback_data' => "district:$id",
                ]
            ],
            array_keys($districts),
            array_values($districts)
        );
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'ask_district'),
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
                'remove_keyboard' => true,
            ]),
        ]);
    }

    public function handle(Update $update)
    {
        return $this->processDistrict($update, RegisterService::class);
    }

    public function handleUpdate(Update $update)
    {
        return $this->processDistrict($update, UserUpdateService::class);
    }

    protected function processDistrict(Update $update, $serviceClass)
    {
        $callbackQuery = $update->getCallbackQuery();
        $message = $callbackQuery?->getMessage();
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId = $message?->getMessageId();
        $data = $callbackQuery?->getData();

        if (!str_starts_with($data, 'district:') || !is_numeric($districtId = str_replace('district:', '', $data))) {
            $this->sender->handle([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'invalid_district_choice'),
            ]);
            return;
        }

        if ($messageId) {
            $this->sender->delete([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        }
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'district_received'),
        ]);

        $serviceInstance = app($serviceClass);
        if (method_exists($serviceInstance, 'mergeToCache')) {
            $serviceInstance->mergeToCache($chatId, [
                'district_id' => $districtId,
                'state' => 'waiting_for_birthdate',
            ]);
        }

        return app(BirthdateStepHandler::class)->ask($chatId);
    }

}