<?php

namespace App\Telegram\Handlers;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;
use App\Services\FromServiceRequest;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class SocialMedia
{
    protected string $prefix = 'tg_social_link:';

    private array $allowedTypes = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        // 'appstore' => 'App Store',
        // 'googleplay' => 'Google Play',
        'telegram' => 'Telegram',
    ];

    public function __construct(
        private FromServiceRequest $forwarder,
        protected Translator $translator
    ) {
    }

public function handle(Update $update): void
{
    $chatId = $update->getMessage()?->getChat()?->getId()
        ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
    $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

    $links = Cache::store('bot')->remember($this->prefix, now()->addHours(6), function () {
        $baseUrl = config('services.urls.web_service');
        $response = $this->forwarder->forward('POST', $baseUrl, '/telegram/social_links', []);
        if (!$response instanceof Response || !$response->successful()) {
            Log::error('Social links olishda xatolik', [
                'status' => $response instanceof Response ? $response->status() : null,
                'body' => $response instanceof Response ? $response->body() : null,
            ]);
            return [];
        }
        return $response->json() ?? [];
    });

    $keyboard = $this->buildKeyboard($links, $chatId);
    $text = $this->translator->get($chatId, 'social_follow_prompt');
    $replyMarkup = json_encode(['inline_keyboard' => $keyboard]);

    // Xabarni faqat haqiqatan o'zgargan bo'lsa edit qilamiz
    try {
        $currentMessage = Telegram::getChatMessage([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);

        $currentText = $currentMessage->getText() ?? '';
        $currentMarkup = json_encode($currentMessage->getReplyMarkup()?->toArray() ?? []);

        if ($currentText === $text && $currentMarkup === $replyMarkup) {
            Log::info("Xabar o'zgarmagan, editMessageText chaqirilmaydi", ['chat_id' => $chatId]);
            return;
        }
    } catch (\Throwable $e) {
        // Agar oldingi xabarni olishda xatolik bo'lsa, davom etamiz
    }

    try {
        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $replyMarkup,
        ]);
    } catch (\Throwable $e) {
        Log::warning("Xabarni edit qilishda xatolik: {$e->getMessage()}", ['chat_id' => $chatId]);
    }
}

    private function buildKeyboard(array $links, $chatId): array
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
                'text' => $this->translator->get($chatId, 'back'),
                'callback_data' => 'back_to_main_menu',
            ]
        ];

        return $keyboard;
    }
}
