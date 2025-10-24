<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class PromoAction
 *
 * @property int $id
 * @property int|null $promotion_id
 * @property int|null $promo_code_id
 * @property int|null $user_id
 * @property int|null $prize_id
 * @property int|null $platform_id
 * @property int|null $receipt_id
 * @property string $action
 * @property string|null $status
 * @property string|null $message
 * @property \Illuminate\Support\Carbon|null $attempt_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Promotions|null $promotion
 * @property-read PromoCode|null $promoCode
 * @property-read Prize|null $prize
 * @property-read User|null $user
 * @property-read Platform|null $platform
 * @property-read SalesReceipt|null $receipt
 */


class PromoAction extends Model
{
    use HasFactory;

    protected $table = 'promo_actions';

    protected $fillable = [
        'promotion_id',
        'promo_code_id',
        'user_id',
        'prize_id',
        'platform_id',
        'receipt_id',
        'action',
        'status',
        'attempt_time',
        'message',
        'shop_id'
    ];


    protected $casts = [
        'attempt_time' => 'datetime',
    ];
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            // ⚙️ attempt_time avtomatik qo‘yiladi
            if (empty($model->attempt_time)) {
                $model->attempt_time = now();
            }

            // ⚙️ Laravel timestamps fallback
            if (empty($model->created_at)) {
                $model->created_at = now();
            }

            if (empty($model->updated_at)) {
                $model->updated_at = now();
            }
        });
    }
    /* -----------------------------------------------------------------
     | 🧱 ENUMS (Actions & Statuses)
     |------------------------------------------------------------------ */

    public const ACTIONS = [
        'claim',         // ➜ Promokod ishlatilgan (foydalanuvchi tomonidan)
        'edit',          // ➜ Admin tomonidan o‘zgartirish kiritilgan
        'vote',          // ➜ Foydalanuvchi ovoz berish yoki ishtirok harakati
        'block',         // ➜ Harakat yoki foydalanuvchi bloklangan
        'manual_add',    // ➜ Admin tomonidan sovg‘a yoki bonus qo‘lda berilgan
        'auto_win',      // ➜ Promokod avtomatik tarzda yutishga sabab bo‘lgan
        'smart_win',     // ➜ Smart algoritm orqali yutish (smart_random)
        'manual_win',    // ➜ Qo‘lda topshirilishi kerak bo‘lgan sovg‘a (pending)
        'weighted_win',  // ➜ Ehtimollik asosida yutish (weighted_random)
        'points_win',    // ➜ Promobal (bonus ball) yutish yoki olish holati
        'no_win',        // ➜ Yutolmadi — ishtirok muvaffaqiyatsiz
        'points_win', // ➜ Chek skanerlash harakati
    ];

    public const STATUSES = [
        'pending',             // ➜ Jarayon kutilmoqda yoki tekshirilmoqda
        'blocked',             // ➜ Harakat to‘xtatilgan yoki foydalanuvchi bloklangan
        'confirmed',           // ➜ Harakat tasdiqlangan (muvaffaqiyatli yakun)
        'canceled',            // ➜ Jarayon bekor qilingan

        // --- Platformalarga oid holatlar ---
        'scaner',       // ➜ Chek skanerlash jarayoni

        // --- Promokod orqali ishlov holatlari ---
        'promocode_claim',     // ➜ Promokod allaqachon ishlatilgan
        'promocode_pending',   // ➜ Promokod tekshirilmoqda
        'promocode_invalid',   // ➜ Promokod noto‘g‘ri yoki mavjud emas
        'promocode_win',       // ➜ Promokod orqali yutish holati
        'promocode_fail',      // ➜ Promokod jarayoni xatolik bilan yakunlandi
        'promocode_lose',      // ➜ Promokod yutolmadi (ishtirok muvaffaqiyatsiz)

        // --- Chek skanerlash (scanner) jarayonlari ---
        'scaner_win',
        'scaner_pending',
        'scaner_fail',
        'scaner_invalid',

        // --- SMS orqali ishlov jarayonlari ---
        'sms_claim',           // ➜ SMS kod allaqachon ishlatilgan
        'sms_pending',         // ➜ SMS tekshirilmoqda
        'sms_invalid',         // ➜ SMS noto‘g‘ri yoki mavjud emas
        'sms_win',             // ➜ SMS orqali yutish holati
        'sms_fail',            // ➜ SMS ishlovda tizim xatosi
        'sms_lose',            // ➜ SMS orqali yutolmadi (ishtirok muvaffaqiyatsiz)
    ];

    /* -----------------------------------------------------------------
     | 🔗 RELATIONSHIPS
     |------------------------------------------------------------------ */

    public function promotion()
    {
        return $this->belongsTo(Promotions::class);
    }
    public function shop()
    {
        return $this->belongsTo(PromotionShop::class, 'shop_id');
    }
    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }

    public function userCache()
    {
        return $this->belongsTo(UsersCache::class, 'user_id', 'user_id');
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function receipt()
    {
        return $this->belongsTo(SalesReceipt::class);
    }

    /* -----------------------------------------------------------------
     | ⚙️ ACCESSORS / HELPERS
     |------------------------------------------------------------------ */

    /**
     * Harakatni "Claim → Claim", "smart_win → Smart Win" formatda qaytaradi.
     */
    public function getActionLabelAttribute(): string
    {
        return str_replace('_', ' ', ucfirst($this->action));
    }

    /**
     * Holat uchun badge class (Bootstrap badge) qaytaradi.
     */

    /**
     * Harakat foydalanuvchi tomonidan bajarilganmi?
     */
    public function isUserAction(): bool
    {
        return in_array($this->action, [
            'claim',
            'vote',
            'no_win',
            'points_win',
            'reseipt_scane',
        ], true);
    }

    /**
     * Harakat tizim tomonidan bajarilganmi?
     */
    public function isSystemAction(): bool
    {
        return in_array($this->action, [
            'auto_win',
            'smart_win',
            'manual_win',
            'weighted_win',
            'manual_add',
        ], true);
    }

    /**
     * Yutuqli harakatmi?
     */
    public function isWinningAction(): bool
    {
        return str_contains($this->action, 'win');
    }

    /**
     * PromoAction yaratishda avtomatik `attempt_time` qo‘yish.
     */

    /**
     * Berilgan statusni normalize qilib saqlash (masalan, `win` → `confirmed`)
     */

}
