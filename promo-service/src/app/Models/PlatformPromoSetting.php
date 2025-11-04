<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformPromoSetting extends Model
{
    // Mass-assignment uchun ruxsat berilgan ustunlar
    protected $fillable = [
        'scanner_points',
        'refferal_start_points',
        'refferal_registered_points',
        'win_message',
    ];

    // JSON ustunlarni avtomatik array qilib olish
    protected $casts = [
        'win_message' => 'array',
    ];

    /**
     * Faqat bitta row mavjud deb default sozlamani olish
     *
     * @return self
     */
    public static function default(): self
    {
        return self::firstOrFail();
    }

    /**
     * Scanner yoki referral balli bo‘yicha yutuq xabarini olish
     *
     * @param string|null $locale Til kodi (uz, ru, en, kr)
     * @param int|null $promoCount Promobal soni, default: scanner_points
     * @return string
     */
    public function getWinMessage(?string $locale = null, ?int $promoCount = null): string
    {
        $locale = $locale ?? 'uz';
        $promoCount = $promoCount ?? $this->scanner_points;

        // Tilga mos xabarni olish
        $message = $this->win_message[$locale] ?? $this->win_message['uz'] ?? '';

        // :promo tokenini almashtirish
        return str_replace(':promo', (string) $promoCount, $message);
    }

    /**
     * Referral start points olish
     *
     * @return int
     */
    public function getReferralStartPoints(): int
    {
        return $this->refferal_start_points;
    }

    /**
     * Referral registered points olish
     *
     * @return int
     */
    public function getReferralRegisteredPoints(): int
    {
        return $this->refferal_registered_points;
    }
}
