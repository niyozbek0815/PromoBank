<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionShop extends Model
{
    protected $fillable = [
        'promotion_id',
        'name',
        'adress',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }
    public function products()
    {
        return $this->hasMany(PromotionProduct::class, 'shops_id');
    }
}
