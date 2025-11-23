<?php
namespace App\Telegram\Services;

use Normalizer;
class NormalizeTextService
{
    public function normalizeText(string $text): string
    {
        // Apostroflarning barcha variantlarini yagona `'` ga keltiramiz
        $apostrophes = [
            '‘',
            '’',
            'ʼ',
            'ʻ',
            'ʽ',
            'ˊ',
            '`',
            '´',
            '′',
            'ʹ',
            '＇'
        ];
        $text = str_replace($apostrophes, "'", $text);

        // Harflarni standart lotincha o‘zbek ko‘rinishiga keltirish
        $map = [
            'о\'' => "o'",
            'О\'' => "O'",
            'ғ' => "g'",
            'Ғ' => "G'",
            'ў' => "o'",
            'Ў' => "O'",
            'қ' => "q",
            'Қ' => "Q",
            'ҳ' => "h",
            'Ҳ' => "H",
            'ъ' => "'",
            'ь' => "",
        ];

        $normalized = strtr($text, $map);

        // Bo‘sh joylarni tozalash (bir nechta joy -> bitta)
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        // Unicode normalization (tavsiya etiladi)
        if (class_exists('Normalizer')) {
            $normalized = normalizer_normalize($normalized, Normalizer::FORM_C) ?: $normalized;
        }

        return trim($normalized);
    }
}