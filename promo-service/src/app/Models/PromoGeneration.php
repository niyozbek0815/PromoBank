<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoGeneration extends Model
{
    protected $fillable = [
        'promotion_id',
        'created_by_user_id',
        'type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Bog'liq promotion
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotions::class);
    }

    /**
     * Ushbu generation orqali yaratilgan promo codlar
     */
    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class, 'generation_id');
    }

    /**
     * type: enum ['generated', 'import']
     */
    public function isImported(): bool
    {
        return $this->type === 'import';
    }

    public function isGenerated(): bool
    {
        return $this->type === 'generated';
    }

    /**
     * Status tekshiruvlari
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    public function isInactive(): bool
    {
        return $this->status === false;
    }
}
