<?php
namespace App\Telegram\Handlers\Start;

use App\Telegram\Services\RegisterRouteService;
use App\Telegram\Services\RegisterService;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartHandler
{
    public function __construct(
        protected Translator $translator
    ) {
        // Constructor can be used for dependency injection if needed
    }
    public function startAsk($chatId)
    {
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'start'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);

        app(RegisterService::class)->mergeToCache($chatId, [
            'chat_id' => $chatId,
            'state'   => 'waiting_for_language',
        ]);

        Log::info("StartHandler startAsk ishladi: $chatId");
        return app(abstract :RegisterRouteService::class)->askNextStep($chatId);

    }
    public function ask($chatId)
    {
        Telegram::sendMessage([
            'chat_id'      => $chatId,
            'text'         => $this->translator->get($chatId, 'welcome'),
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
            ]),
        ]);

        $lang = json_decode(Cache::store('redis')->get("tg_lang:$chatId"), true) ?? [];

        if (empty($lang)) {
            Telegram::sendMessage([
                'chat_id'      => $chatId,
                'text'         => "🌐 Iltimos, tilni tanlang:\n🌐 Пожалуйста, выберите язык:\n🌐 Илтимос, тилни танланг:",
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => "🇺🇿 O‘zbekcha", 'callback_data' => 'lang:uz'],
                            ['text' => "🇷🇺 Русский", 'callback_data' => 'lang:ru'],
                            ['text' => "🇺🇿 Кирилл", 'callback_data' => 'lang:kr'],
                        ],
                    ],
                ]),
            ]);
            return;
        }

        return app(RegisterRouteService::class)->askNextStep($chatId);

    }

}
