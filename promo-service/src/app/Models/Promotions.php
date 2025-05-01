<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Promotions extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'company_id',
        'title',
        'description',
        'is_active',
        'is_public',
        'code_settings',
        'extra_conditions',
        'start_date',
        'end_date',
        'created_by_user_id',
        'status',
    ];
    public $translatable = ['name', 'title', 'description'];
    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'code_settings' => 'array',
        'extra_conditions' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }



    public function users()
    {
        return $this->belongsToMany(User::class, 'promotion_users', 'promotion_id', 'user_id');
    }
    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'platform_promotion');
    }
    public function winnerSelectionTypes()
    {
        return $this->belongsToMany(WinnerSelectionType::class, 'promotion_winner_selection_type');
    }
    public function promoGenerations()
    {
        return $this->hasMany(PromoGeneration::class);
    }
    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class, 'promotion_id');
    }
    public function promotionShops()
    {
        return $this->hasMany(PromotionShop::class, 'promotion_id');
    }
    public function promotionProducts()
    {
        return $this->hasMany(PromotionProduct::class, 'promotion_id');
    }
    public function setting()
    {
        return $this->hasOne(PromotionSetting::class);
    }
}
