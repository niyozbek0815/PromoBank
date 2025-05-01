<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'length',
        'charset',
        'exclude_chars',
        'prefix',
        'suffix',
        'unique_across_all_promotions',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class);
    }
}
