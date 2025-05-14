<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionProduct extends Model
{
    protected $fillable = [
        'promotion_id',
        'shop_id',
        'name',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }

    public function shop()
    {
        return $this->belongsTo(PromotionShop::class, 'shop_id');
    }
}