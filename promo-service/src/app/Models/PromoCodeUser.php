<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeUser extends Model
{
    protected $table = 'promo_user';

    protected $fillable = [
        'promo_codes_id',
        'user_id',
        'receipt_id',
        'platform_id',
    ];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class, 'promo_codes_id');
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
