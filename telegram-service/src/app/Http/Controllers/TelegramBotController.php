<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $update = Telegram::getWebhookUpdate();

        $chatId = $update->getMessage()?->getChat()?->getId();
        if ($callback = $update->getCallbackQuery()) {
            return $this->handleCallback($callback);
        }

        // Agar /start komandasi yuborilgan bo‘lsa
        if ($update->getMessage()?->getText() === '/start') {
            return $this->sendLanguageSelection($chatId); // to‘g‘ri funksiya shu
        }

        // Agar user contact yuborgan bo‘lsa
        if ($update->getMessage()?->getContact()) {
            return $this->handleContact($update->getMessage());
        }

        // Default javob
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "Iltimos, ro'yhatdan o'tish uchun 📱 telefon raqamingizni yuboring",
        ]);

        return response()->noContent();
    }
    protected function trans($chatId, $key)
    {
        $lang = Cache::store('redis')->get("tg_lang:$chatId", 'uz');

        $messages = [
            'ask_phone' => [
                'uz' => "📱 Iltimos, ro'yhatdan o'tish uchun telefon raqamingizni yuboring",
                'ru' => "📱 Пожалуйста, отправьте свой номер телефона для регистрации",
                'kr' => "📱 Илтимос, рўйхатдан ўтиш учун телефон рақамингизни юборинг",
            ],
            'already_registered' => [
                'uz' => "✅ Siz muvaffaqiyatli ro'yxatdan o'tgansiz.",
                'ru' => "✅ Вы успешно зарегистрированы.",
                'kr' => "✅ Сиз муваффақиятли рўйхатдан ўтдингиз.",
            ],
            'ask_region' => [
                'uz' => "📍 Iltimos, yashash hududingizni tanlang.",
                'ru' => "📍 Пожалуйста, выберите ваш регион.",
                'kr' => "📍 Илтимос, яшаш ҳудудингизни танланг.",
            ],
            'share_phone_button' => [
                'uz' => '📱 Raqamni yuborish',
                'ru' => '📱 Отправить номер',
                'kr' => '📱 Рақамни юбориш',
            ],
        ];

        return $messages[$key][$lang] ?? $messages[$key]['uz'];
    }
    protected function handleCallback($callback)
    {
        $chatId = $callback->getMessage()->getChat()->getId();
        $data = $callback->getData();

        if (str_starts_with($data, 'lang_')) {
            $lang = str_replace('lang_', '', $data);
            Cache::store('redis')->put("tg_lang:$chatId", $lang, now()->addDays(7));

            Telegram::answerCallbackQuery([
                'callback_query_id' => $callback->getId()
            ]);

            Telegram::deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $callback->getMessage()->getMessageId()
            ]);

            return $this->sendPhoneRequestMessage($chatId);
        }

        return response()->noContent();
    }
    protected function sendPhoneRequestMessage($chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $this->trans($chatId, 'ask_phone'),
            'reply_markup' => json_encode([
                'keyboard' => [
                    [
                        [
                            'text' => $this->trans($chatId, 'share_phone_button'),
                            'request_contact' => true
                        ]
                    ]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]),
        ]);

        return response()->noContent();
    }
    protected function handleContact($message)
    {
        $chatId = $message->getChat()->getId();
        $phone = $message->getContact()->getPhoneNumber();
        $phone = strpos($phone, '+') !== 0 ? '+' . $phone : $phone;

        $userExists = $this->checkIfUserExists($phone);

        if ($userExists) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $this->trans($chatId, 'already_registered'),
            ]);
        } else {
            Cache::store('redis')->put("tg_reg_state:$chatId", 'waiting_for_region', now()->addDays(7));
            Cache::store('redis')->put("tg_reg_data:$chatId:phone", $phone, now()->addDays(7));

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $this->trans($chatId, 'ask_region'),
            ]);
        }

        return response()->noContent();
    }
    protected function checkIfUserExists(string $phone): bool
    {
        // Misol uchun Auth service orqali tekshirish
        try {
            // $response = Http::auth()->get("/api/users/check", ['phone' => $phone]);

            return true;
        } catch (\Exception $e) {
            Log::error("User check error: " . $e->getMessage());
            return false;
        }
    }
    protected function sendLanguageSelection($chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "Iltimos, tilni tanlang:\nПожалуйста, выберите язык:\n언어를 선택해주세요:",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "🇺🇿 O‘zbekcha", 'callback_data' => 'lang_uz'],
                        ['text' => "🇷🇺 Русский", 'callback_data' => 'lang_ru'],
                        ['text' => "🇺🇿 Кирилл", 'callback_data' => 'lang_kr'],
                    ]
                ]
            ])
        ]);
    }
}
