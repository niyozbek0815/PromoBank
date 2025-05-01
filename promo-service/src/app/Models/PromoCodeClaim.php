<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeClaim extends Model
{
    protected $fillable = [
        'promo_code_id',
        'user_id',
        'status',
        'attempt_time',
    ];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
