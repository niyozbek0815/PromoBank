<?php

namespace App\Telegram\Handlers;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Telegram\Services\Translator;
use App\Telegram\Services\UserSessionService;
use Illuminate\Support\Facades\Cache;

class ContactHandler
{
    public function handle($message)
    {
        $chatId = $message->getChat()->getId();
        $phone = $message->getContact()->getPhoneNumber();
        $phone = strpos($phone, '+') !== 0 ? '+' . $phone : $phone;

        $translator = app(Translator::class);
        $userSession = app(UserSessionService::class);
        $user_created =  $userSession->bindChatToUser($chatId, $phone);

        if ($user_created) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $translator->get($chatId, 'ask_region'),
                'reply_markup' => json_encode([
                    'remove_keyboard' => true
                ])
            ]);
        } else {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $translator->get($chatId, 'already_registered'),
                'reply_markup' => json_encode([
                    'remove_keyboard' => true
                ])
            ]);
            $text = Cache::store('redis')->pull("tg_pending:$chatId");

            if ($text) {
                app(abstract: \App\Telegram\Services\PendingCommandRunner::class)->run($chatId, $text);
            } else {
                return;
            }
        }
    }
}
