<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    protected $fillable = [
        'promotion_id',
        'type_id',
        'category_id',
        'index',
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
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (is_null($product->index)) {
                // Index kiritilmagan, oxiriga qo‘shiladi
                $maxIndex = static::where('promotion_id', $product->promotion_id)->max('index') ?? 0;
                $product->index = $maxIndex + 1;
            } else {
                // Index kiritilgan va mavjud bo‘lishi mumkin
                $conflictExists = static::where('promotion_id', $product->promotion_id)
                    ->where('index', '>=', $product->index)
                    ->exists();

                if ($conflictExists) {
                    // To‘qnashuv bo‘lsa, barcha keyingi indexlarni 1 ga oshiramiz
                    static::where('promotion_id', $product->promotion_id)
                        ->where('index', '>=', $product->index)
                        ->orderByDesc('index') // yuqoridan pastga qarab
                        ->get()
                        ->each(function ($item) {
                            $item->index += 1;
                            $item->save();
                        });
                }
            }
        });
    }

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
