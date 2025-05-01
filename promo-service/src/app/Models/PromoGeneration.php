<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoGeneration extends Model
{
    protected $fillable = [
        'promotion_id',
        'count',
        'created_by_user_id',
        'status',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class);
    }
    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class, 'generation_id');
    }
}
