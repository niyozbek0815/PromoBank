<?php
namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Promotions extends Model
{
    use HasFactory, HasTranslations, HasMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'company_id',
        'title',
        'description',
        'status',
        'is_public',
        'is_prize',
        'code_settings',
        'extra_conditions',
        'start_date',
        'end_date',
        'created_by_user_id',
        'status',
    ];
    public $translatable = ['name', 'title', 'description'];
    protected $casts     = [
        'status'           => 'boolean',
        'is_public'        => 'boolean',
        'code_settings'    => 'array',
        'name'             => "array",
        'extra_conditions' => 'array',
        'start_date'       => 'datetime',
        'end_date'         => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->select(['id', 'name']);
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
    public function prizePromos()
    {
        return $this->hasMany(PrizePromo::class);
    }

    public function participationTypes()
    {
        return $this->hasMany(PromotionParticipationType::class, 'promotion_id')->with('participationType');
    }

    public function participantTypeIds()
    {
        return $this->belongsToMany(ParticipationType::class, 'promotion_participation_types', 'promotion_id', 'participation_type_id');
    }

    public function participationTypesSms()
    {
        return $this->hasMany(PromotionParticipationType::class, 'promotion_id')->with('participationType')->whereHas('participationType', function ($query) {
            $query->where('slug', 'sms');
        });
    }
    public function platformPromotions()
    {
        return $this->hasMany(PlatformPromotion::class, 'promotions_id');
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'platform_promotions', 'promotion_id', 'platform_id')
            ->withPivot(['is_enabled', 'additional_rules'])
            ->withTimestamps();
    }

    public function platformIds()
    {
        return $this->belongsToMany(Platform::class, 'platform_promotions', 'promotion_id', 'platform_id');
    }

    public function promoActions()
    {
        return $this->hasMany(PromoAction::class, 'promotion_id');
    }
    public function shops()
    {
        return $this->hasMany(PromotionShop::class, 'promotion_id');
    }
}
