<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegionsAndDistrictService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Illuminate\Support\Facades\Cache;
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
        $lang = Cache::store('bot')->get("tg_lang:$chatId", 'uz');

        Log::info("Regions", ['regions' => $regions]);

        if (empty($regions)) {
            $this->sendMessage($chatId, 'region_list_failed');
            return;
        }

        // Kerakli til bo'yicha massiv yaratish
        $regionsByLang = [];
        foreach ($regions as $id => $names) {
            $regionsByLang[] = [
                'id' => $id,
                'name' => $names[$lang] ?? $names['uz'], // tanlangan til yoki fallback
            ];
        }

        // Alifbo bo'yicha sort qilish
        usort($regionsByLang, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        // Inline keyboard yaratish
        $keyboard = array_map(
            fn($region) => [
                [
                    'text' => $region['name'],
                    'callback_data' => "region:{$region['id']}"
                ]
            ],
            $regionsByLang
        );

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'ask_region'),
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
                'remove_keyboard' => true,
            ]),
        ]);
    }

    protected function processRegion(Update $update, $cacheService)
    {
        $callbackQuery = $update->getCallbackQuery();
        $message = $callbackQuery?->getMessage();
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId = $message?->getMessageId();
        $data = $callbackQuery?->getData();

        if (!str_starts_with($data, 'region:') || !is_numeric($regionId = str_replace('region:', '', $data))) {
            $this->sendMessage($chatId, 'invalid_region_choice');
            return;
        }

        $regionId = (int) $regionId;
        Log::info("handle region_id: $regionId");
        if ($messageId) {
            Telegram::deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        }
        $this->sendMessage($chatId, 'region_received');

        app($cacheService)->mergeToCache($chatId, [
            'region_id' => $regionId,
            'state' => 'waiting_for_birthdate',
            // 'state'     => 'waiting_for_district',
        ]);
        return app(BirthdateStepHandler::class)->ask($chatId);

        // return app(DistrictStepHandler::class)->ask($chatId, $regionId);
    }

    public function handle(Update $update)
    {
        return $this->processRegion($update, RegisterService::class);
    }

    public function handleUpdate(Update $update)
    {
        return $this->processRegion($update, UserUpdateService::class);
    }

    protected function sendMessage($chatId, $key)
    {
        if (empty($chatId)) {
            Log::warning("sendMessage chaqirildi, lekin chatId bo'sh. Key: $key");
            return;
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, $key),
        ]);
    }
}