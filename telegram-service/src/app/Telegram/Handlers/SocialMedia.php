<?php

namespace App\Telegram\Handlers;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;
use App\Services\FromServiceRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class SocialMedia
{
    protected string $prefix = 'tg_social_link:';

    private array $allowedTypes = [
        'instagram' => '📸 Instagram',
        'facebook' => '📘 Facebook',
        'youtube' => '▶️ YouTube',
        'appstore' => '🍏 App Store',
        'googleplay' => '📲 Google Play',
        // 'telegram' => '📢 Telegram kanal',
    ];

    public function __construct(private FromServiceRequest $forwarder)
    {
    }

    public function handle(Update $update): void
    {
        $baseUrl = config('services.urls.web_service');
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

        // Avval cache'dan olishga urinib ko‘ramiz
        $cacheKey = $this->prefix . $chatId;
        $links = Cache::store('redis')->get($cacheKey);

        if (!$links) {
            $response = $this->forwarder->forward('POST', $baseUrl, '/telegram/social_links', []);

            if (!$response instanceof Response || !$response->successful()) {
                Log::error('Social links olishda xatolik', [
                    'status' => $response instanceof Response ? $response->status() : null,
                    'body' => $response instanceof Response ? $response->body() : null,
                ]);
                return;
            }

            $links = $response->json() ?? [];

            // Cache’ga saqlaymiz (masalan 6 soat)
            Cache::store('redis')->put($cacheKey, json_encode($links), now()->addHours(6));
        } else {
            $links = json_decode($links, true);
        }

        Log::info("Social links", ['data' => $links]);

        $keyboard = $this->buildKeyboard($links);

        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "📱 Bizning ijtimoiy tarmoqlarimizga azo bo'ling va kuzatib boring:",
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function buildKeyboard(array $links): array
    {
        $keyboard = [];

        foreach ($links as $link) {
            $type = $link['type'] ?? null;
            $url = $link['url'] ?? null;

            if (!$type || !$url || !isset($this->allowedTypes[$type])) {
                continue;
            }

            if (empty($keyboard) || count(end($keyboard)) >= 2) {
                $keyboard[] = [];
            }

            $keyboard[array_key_last($keyboard)][] = [
                'text' => $this->allowedTypes[$type],
                'url' => $url,
            ];
        }

        $keyboard[] = [
            [
                'text' => "⬅️ Ortga",
                'callback_data' => 'back_to_main_menu',
            ]
        ];

        return $keyboard;
    }
}
