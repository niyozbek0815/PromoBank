<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegionsAndDistrictService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
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
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'region_list_failed'),
            ]);
            return;
        }

        $keyboard = array_map(fn($id, $name) => [[
            'text'          => $name,
            'callback_data' => "district:$id",
        ]], array_keys($districts), array_values($districts));

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
        $callbackQuery = $update->getCallbackQuery();
        $message       = $callbackQuery?->getMessage();
        $chatId        = $message?->getChat()?->getId();
        $messageId     = $message?->getMessageId();
        $data          = $callbackQuery?->getData();
        if (! str_starts_with($data, 'district:') || ! is_numeric($districtId = str_replace('district:', '', $data))) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'invalid_region_choice'),
            ]);
            return;
        }

        // Cache::store('redis')->put("tg_reg_data:$chatId:district_id", $districtId);
        if ($messageId) {
            Telegram::deleteMessage([
                'chat_id'    => $chatId,
                'message_id' => $messageId,
            ]);
        }
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text'    => $this->translator->get($chatId, 'district_received'),
        ]);

        app(RegisterService::class)->mergeToCache($chatId, [
            'district_id' => $districtId,
            'state'       => 'waiting_for_birthdate',
        ]);

        return app(BirthdateStepHandler::class)->ask($chatId);
    }
}
