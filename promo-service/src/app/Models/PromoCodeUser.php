<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeUser extends Model
{
    protected $table = 'promo_code_users';

    protected $fillable = [
        'promo_code_id',
        'user_id',
        'receipt_id',
        'platform_id',
        'promotion_product_id',
        'prize_id',
        'sub_prize_id'
    ];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }


    public function receipt()
    {
        return $this->belongsTo(SalesReceipt::class, "receipt_id");
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
    public function promotionProduct()
    {
        return $this->belongsTo(PromotionProduct::class, 'promotion_product_id');
    }
}
