<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegionsAndDistrictService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class RegionStepHandler
{
    public function __construct(protected Translator $translator)
    {
    }
    public function ask($chatId)
    {
        $regions = app(RegionsAndDistrictService::class)->handle();

        if (empty($regions)) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'region_list_failed'),
            ]);
            return;
        }

        $keyboard = array_map(fn($id, $name) => [[
            'text'          => $name,
            'callback_data' => "region:$id",
        ]], array_keys($regions), array_values($regions));

        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'ask_region'), // translator orqali tarjima
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

        if (! str_starts_with($data, 'region:') || ! is_numeric($regionId = str_replace('region:', '', $data))) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => $this->translator->get($chatId, 'invalid_region_choice'),
            ]);
            return;
        }

        $regionId = (int) $regionId;
        Log::info("handle region_id: $regionId");
        if ($messageId) {
            Telegram::deleteMessage([
                'chat_id'    => $chatId,
                'message_id' => $messageId,
            ]);
        }

        app(RegisterService::class)->mergeToCache($chatId, [
            'region_id' => $regionId,
            'state'     => 'waiting_for_district',
        ]);

        return app(DistrictStepHandler::class)->ask($chatId, $regionId);
    }
}
