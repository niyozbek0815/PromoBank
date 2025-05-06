<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartRandomValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'prize_id',
        'rule_id',
        'operator',
        'values',
    ];

    protected $casts = [
        'values' => 'array',
    ];

    // 🎯 Har bir qator bitta Prize'ga tegishli
    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }

    // 🔁 Har bir qator bitta SmartRandomRule'ga tegishli
    public function rule()
    {
        return $this->belongsTo(SmartRandomRule::class, 'rule_id');
    }
}
