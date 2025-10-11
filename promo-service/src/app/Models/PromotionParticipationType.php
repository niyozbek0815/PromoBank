<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionParticipationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'participation_type_id',
        'is_enabled',
        'additional_rules'
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'additional_rules' => 'array',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }

    public function participationType()
    {
        return $this->belongsTo(ParticipationType::class);
    }
}
