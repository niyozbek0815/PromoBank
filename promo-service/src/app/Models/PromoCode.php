<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'generation_id',
        'promotion_id',
        'promocode',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function generation()
    {
        return $this->belongsTo(PromoGeneration::class, 'generation_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }

    public function prizePromos()
    {
        return $this->hasMany(PrizePromo::class);
    }
    public function actions()
    {
        return $this->hasMany(PromoAction::class, 'promo_code_id')
            ->with([
                'userCache:id,user_id,name,phone,status',
                'prize:id,name',
                'platform:id,name', // ✅ Platform qo‘shildi
            ]);
    }

    public function codeUsers()
    {
        return $this->hasMany(PromoCodeUser::class, 'promo_code_id')
            ->with([
                'userCache:id,user_id,name,phone,status',
                'platform:id,name',
                'promotion:id,name',
                'prize:id,name',
            ]);
    }

}
