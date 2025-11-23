<?php
namespace App\Telegram\Handlers\Register;

use App\Telegram\Services\RegionsAndDistrictService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserUpdateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class RegionStepHandler
{
    public function __construct(protected Translator $translator, protected SendMessages $sender)
    {
    }

    public function ask($chatId)
    {
        $regions = app(RegionsAndDistrictService::class)->handle();
        $lang = Cache::store('bot')->get("tg_lang:$chatId", 'uz');
        if (empty($regions)) {
            return;
        }
        $regionsByLang = [];
        foreach ($regions as $id => $names) {
            $regionsByLang[] = [
                'id' => $id,
                'name' => $names[$lang] ?? $names['uz'], // tanlangan til yoki fallback
            ];
        }
        usort($regionsByLang, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        $keyboard = array_map(
            fn($region) => [
                [
                    'text' => $region['name'],
                    'callback_data' => "region:{$region['id']}"
                ]
            ],
            $regionsByLang
        );
        $this->sender->handle([
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
            $this->sender->handle([
                'chat_id' => $chatId,
                'text' => $this->translator->get($chatId, 'invalid_region_choice')
            ]);
            return;
        }

        $regionId = (int) $regionId;
        Log::info("handle region_id: $regionId");
        if ($messageId) {
            $this->sender->delete([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        }
        $this->sender->handle([
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'region_received'),
        ]);

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

}
