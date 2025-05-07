<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizePromo extends Model
{
    protected $fillable = [
        'promotion_id',
        'prize_id',
        'category_id',
        'promo_code_id',
        'sub_prize',
        'is_used'
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class);
    }

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }

    public function category()
    {
        return $this->belongsTo(PrizeCategory::class, 'category_id');
    }

    // public function promoCode()
    // {
    //     return $this->belongsTo(PromoCode::class);
    // }

}