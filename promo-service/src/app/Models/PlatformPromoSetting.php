<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformPromoSetting extends Model
{
    protected $fillable = [
        'default_points',
        'win_message',
    ];

    protected $casts = [
        'win_message' => 'array', // JSON -> array
    ];

    /**
     * Default sozlamani olish (faqat bitta row mavjud deb faraz qilinadi)
     */
    public static function default(): self
    {
        return self::firstOrFail();
    }

    /**
     * Mahalliy tilga mos xabar va :promo tokenini almashtirish
     *
     * @param string|null $locale
     * @param int|null $promoCount
     * @return string
     */
    public function getWinMessage(?string $locale = null): string
    {
        $locale = $locale ?? 'uz';

        // default_points va win_message birgalikda ishlatiladi
        $promoCount = $this->default_points;

        // tilga mos xabarni olish
        $message = $this->win_message[$locale] ?? $this->win_message['uz'] ?? '';

        return str_replace(':promo', $promoCount, $message);
    }
}
