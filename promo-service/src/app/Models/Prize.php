<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    protected $fillable = [
        'promotion_id',
        'type_id',
        'category_id',
        'name',
        'description',
        'quantity',
        'daily_limit',
        'is_active',
        'created_by_user_id',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'prize_message' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class);
    }


    public function category()
    {
        return $this->belongsTo(PrizeCategory::class, 'category_id');
    }
    public function subPrizes()
    {
        return $this->hasMany(SubPrize::class);
    }
    public function message()
    {
        return $this->hasMany(PrizeMessage::class);
    }
    public function prizePromos()
    {
        return $this->hasMany(PrizePromo::class);
    }
    public function prizeUsers()
    {
        return $this->hasMany(PromoCodeUser::class, 'prize_id', 'id');
    }
    public function promoActions()
    {
        return $this->hasMany(PromoAction::class);
    }
    public function smartRandomValues()
    {
        return $this->hasMany(SmartRandomValue::class);
    }
}
