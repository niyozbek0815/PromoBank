<?php

namespace App\Telegram\Services;

use Illuminate\Support\Facades\Cache;

class Translator
{
    protected array $messages = [
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
        'welcome' => [
            'uz' => 'Promobankga xush kelibsiz',
            'ru' => ' Ruscha: Добро пожаловать в Промобанк',
            'kr' => 'Промобанкка хуш келибсиз',
        ],
    ];

    public function get($chatId, $key)
    {
        $lang = Cache::store('redis')->get("tg_lang:$chatId", 'uz');
        return $this->messages[$key][$lang] ?? $this->messages[$key]['uz'];
    }
}