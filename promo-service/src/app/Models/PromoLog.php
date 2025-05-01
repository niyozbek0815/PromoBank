<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoLog extends Model
{
    protected $fillable = [
        'promotion_id',
        'promo_code_id',
        'user_id',
        'prize_id',
        'action',
        'message',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class);
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
