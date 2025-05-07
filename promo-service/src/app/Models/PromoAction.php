<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoAction extends Model
{
    protected $fillable = [
        'promotion_id',
        'promo_code_id',
        'user_id',
        'prize_id',
        'action',
        'status',
        'attempt_time',
        'message',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }


    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }
}
