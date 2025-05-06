<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartRandomRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'input_type',
        'is_comparison',
    ];
    protected $casts = [
        'is_comparison' => 'boolean',
    ];
    // 🧷 Rule bir nechta PrizeRuleValue bilan bog‘langan
    public function RuleValues()
    {
        return $this->hasMany(SmartRandomValue::class, 'rule_id');
    }
}
