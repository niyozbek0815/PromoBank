<?php
namespace App\Telegram\Handlers;

use App\Telegram\Services\Translator;
use App\Telegram\Services\UserSessionService;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class ProfilSettings
{
    public function __construct(protected Translator $translator)
    {
        // Constructor can be used for dependency injection if needed
    }
    public function handle(Update $update)
    {
        $chatId    = $update->getMessage()?->getChat()?->getId() ?? $update->getCallbackQuery()?->getMessage()?->getChat()?->getId();
        $messageId = $update->getCallbackQuery()?->getMessage()?->getMessageId();
        $user      = app(UserSessionService::class)->get($chatId);
        $lang      = Cache::store('redis')->get("tg_lang:$chatId", 'uz');

        $text = "📋 <b>" . $this->translator->get($chatId, 'profile_title') . "</b>\n\n" .
        "👤 <b>" . $this->translator->get($chatId, 'profile_name') . ":</b> {$user['name']}\n" .
        "📞 <b>" . $this->translator->get($chatId, 'profile_phone') . ":</b> {$user['phone']}\n" .
        ($user['phone2'] ? "📞 <b>" . $this->translator->get($chatId, 'profile_phone2') . ":</b> {$user['phone2']}\n" : '') .
        "📍 <b>" . $this->translator->get($chatId, 'profile_region') . ":</b> {$user['region']}\n" .
        "🏘 <b>" . $this->translator->get($chatId, 'profile_district') . ":</b> {$user['district']}\n" .
        "⚧ <b>" . $this->translator->get($chatId, 'profile_gender') . ":</b> " . ($user['gender'] === 'M' ? $this->translator->get($chatId, 'gender_male') : $this->translator->get($chatId, 'gender_female')) . "\n" .
        "📅 <b>" . $this->translator->get($chatId, 'profile_birthdate') . ":</b> " . date('d.m.Y', strtotime($user['birthdate'])) . "\n" .
            "🌐 <b>" . $this->translator->get($chatId, 'profile_lang') . ":</b> "
            . $this->translator->getForLang('language_selection', $lang)
            . "\n";

        $currentMessage = $update->getCallbackQuery()?->getMessage()?->getText();
        if ($currentMessage === $text) {
            return;
        }

        Telegram::editMessageText([
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'text'         => $text,
            'parse_mode'   => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => $this->translator->get($chatId, 'profile_update'), 'callback_data' => 'edit_profile'],
                    ],
                    [
                        ['text' => $this->translator->get($chatId, 'back'), 'callback_data' => 'back_to_main_menu'],
                    ],
                ],
            ]),
        ]);
    }
}
