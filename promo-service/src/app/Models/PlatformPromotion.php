<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformPromotion extends Model
{
    use HasFactory;
    protected $fillable = [
        'promotion_id',
        'platform_id',
        'phone',
        'is_enabled',
        'additional_rules',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'additional_rules' => 'array',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }
}
