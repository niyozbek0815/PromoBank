<?php

namespace App\Telegram\Handlers;

use App\Telegram\Services\SendMessages;
use Telegram\Bot\Objects\Update;
use App\Services\FromServiceRequest;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class SocialMedia
{
    private array $allowedTypes = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'telegram' => 'Telegram',
    ];

    public function __construct(
        private FromServiceRequest $forwarder,
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }

    public function handle(Update $update): void
    {
        $chatId = $update->getMessage()?->getChat()?->getId()
            ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();

        $links = Cache::store('bot')->remember('tg_social_link', now()->addHours(6), fn() => $this->fetchLinks());

        $keyboard = $this->buildKeyboard($links, $chatId);
        $text = $this->translator->get($chatId, 'social_follow_prompt');

        $this->sender->editMessage([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function fetchLinks(): array
    {
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
    }

    private function buildKeyboard(array $links, int $chatId): array
    {
        $buttons = array_filter(array_map(
            fn($link) =>
            isset($this->allowedTypes[$link['type'] ?? '']) && !empty($link['url'])
            ? ['text' => $this->allowedTypes[$link['type']], 'url' => $link['url']]
            : null
            ,
            $links
        ));

        // 2 ta tugma qator bilan bo‘lish
        $keyboard = array_chunk($buttons, 2);

        // Back tugmasini qo‘shish
        $keyboard[] = [
            [
                'text' => $this->translator->get($chatId, 'back'),
                'callback_data' => 'back_to_main_menu'
            ]
        ];

        return $keyboard;
    }
}
