<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReceipt extends Model
{
    use HasFactory;

    protected $table = 'sales_receipts';

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'chek_id',
        'nkm_number',
        'sn',
        'check_date',
        'qqs_summa',
        'summa',
        'lat',
        'long',
        'payment_type',
    ];

    protected $casts = [
        'check_date' => 'datetime',
        'qqs_summa' => 'decimal:2',
        'summa' => 'decimal:2',
        'lat' => 'decimal:8',
        'long' => 'decimal:8',
    ];

    /* -----------------------------------------------------------------
     | 🔗 RELATIONSHIPS
     |------------------------------------------------------------------ */

    /**
     * Chekdagi mahsulotlar
     */
    public function products(): HasMany
    {
        return $this->hasMany(SalesProduct::class, 'receipt_id');
    }
    public function encouragementPoints()
    {
        return $this->morphMany(EncouragementPoint::class, 'scope');
    }
    /**
     * Chekka tegishli foydalanuvchi cache ma’lumotlari
     */
    public function userCache(): BelongsTo
    {
        return $this->belongsTo(UsersCache::class, 'user_id', 'user_id');
    }

    /**
     * Chek orqali bog‘langan promo-code foydalanuvchilar
     */
    public function promoCodeUsers(): HasMany
    {
        return $this->hasMany(PromoCodeUser::class, 'receipt_id');
    }

    /**
     * Chekka tegishli harakatlar (PromoAction loglari)
     */
    public function actions(): HasMany
    {
        return $this->hasMany(PromoAction::class, 'receipt_id');
    }

    /**
     * Rag‘bat ballari
     */

    /* -----------------------------------------------------------------
     | ⚙️ ACCESSORS / HELPERS
     |------------------------------------------------------------------ */

    /**
     * To‘liq summa formatda qaytaradi
     */
    public function getFormattedSummaAttribute(): string
    {
        return number_format($this->summa ?? 0, 2, '.', ' ');
    }

    /**
     * Chek sanasini formatlab beradi (d.m.Y H:i)
     */
    public function getFormattedDateAttribute(): ?string
    {
        return $this->check_date ? $this->check_date->format('d.m.Y H:i') : null;
    }

    /**
     * Harakatlar soni
     */
    public function getActionsCountAttribute(): int
    {
        return $this->actions()->count();
    }

    /**
     * Promokodlar soni
     */
    public function getPromoCodesCountAttribute(): int
    {
        return $this->promoCodeUsers()->count();
    }

    /**
     * Foydalanuvchi nomi (UsersCache orqali)
     */
    public function getUserNameAttribute(): ?string
    {
        return $this->userCache?->name;
    }

    /**
     * Foydalanuvchi telefoni (UsersCache orqali)
     */
    public function getUserPhoneAttribute(): ?string
    {
        return $this->userCache?->phone;
    }
}
