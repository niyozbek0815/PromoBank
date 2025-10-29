<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegionsAndDistrictService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class DistrictStepHandler
{
    public function __construct(protected Translator $translator)
    {
    }

    public function ask($chatId, $region_id)
    {
        $districts = app(RegionsAndDistrictService::class)->district($region_id);
        if (empty($districts)) {
            $this->sendMessage($chatId, 'region_list_failed');
            return;
        }

        $keyboard = array_map(
            fn($id, $name) => [[
                'text'          => $name,
                'callback_data' => "district:$id",
            ]],
            array_keys($districts),
            array_values($districts)
        );

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'ask_district'),
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
        $message       = $callbackQuery?->getMessage();
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
              $messageId     = $message?->getMessageId();
        $data          = $callbackQuery?->getData();

        if (! str_starts_with($data, 'district:') || ! is_numeric($districtId = str_replace('district:', '', $data))) {
            $this->sendMessage($chatId, 'invalid_district_choice');
            return;
        }

        if ($messageId) {
            Telegram::deleteMessage([
                'chat_id'    => $chatId,
                'message_id' => $messageId,
            ]);
        }

        $this->sendMessage($chatId, 'district_received');

        $serviceInstance = app($serviceClass);
        if (method_exists($serviceInstance, 'mergeToCache')) {
            $serviceInstance->mergeToCache($chatId, [
                'district_id' => $districtId,
                'state'       => 'waiting_for_birthdate',
            ]);
        }

        return app(BirthdateStepHandler::class)->ask($chatId);
    }

    protected function sendMessage($chatId, $key)
    {
        if (empty($chatId)) {
            Log::warning("sendMessage chaqirildi, lekin chatId bo'sh. Key: $key");
            return;
        }
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => $this->translator->get($chatId, $key),
        ]);
    }
}
