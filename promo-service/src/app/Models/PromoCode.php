<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'generation_id',
        'promotion_id',
        'platform_id',
        'promocode',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function generation()
    {
        return $this->belongsTo(PromoGeneration::class, 'generation_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }
    public function claims()
    {
        return $this->hasMany(PromoCodeClaim::class);
    }
}