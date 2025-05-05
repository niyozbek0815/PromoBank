<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function platformPromotions()
    {
        return $this->hasMany(PlatformPromotion::class, 'platform_id');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotions::class, 'platform_promotions')
            ->withPivot(['is_enabled', 'additional_rules'])
            ->withTimestamps();
    }
}
