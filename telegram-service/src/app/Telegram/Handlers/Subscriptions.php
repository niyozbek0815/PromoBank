<?php

namespace App\Telegram\Handlers;

use App\Telegram\Services\SendMessages;
use App\Telegram\Services\Translator;
use Illuminate\Support\Facades\Log;

class Subscriptions
{


    public function __construct(
        protected Translator $translator,
        protected SendMessages $sender
    ) {
    }
    public function showSubscriptionPrompt(
        int $chatId,
        array $notSubscribed,
        ?int $messageId = null,
        string $callback = 'check_subscriptions'
    ): void {
        $keyboard = array_map(
            fn($ch) => [['text' => "🔗 {$ch}", 'url' => "https://t.me/" . ltrim($ch, '@')]],
            $notSubscribed
        );

        $keyboard[] = [
            ['text' => $this->translator->get($chatId, 'check'), 'callback_data' => $callback]
        ];

        $payload = [
            'chat_id' => $chatId,
            'text' => $this->translator->get($chatId, 'subscription_prompt'),
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'HTML',
        ];

        try {
            if ($messageId) {
                $payload['message_id'] = $messageId;
                $this->sender->editMessage($payload);
            } else {
                $this->sender->handle($payload);
            }
        } catch (\Throwable $e) {
            Log::error('Subscription xabarini yuborish/edit qilishda xatolik', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}