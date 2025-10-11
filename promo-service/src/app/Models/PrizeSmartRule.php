<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeSmartRule extends Model
{
    protected $fillable = [
        'prize_id',
        'rule_key',
        'rule_operator',
        'rule_value',
    ];

    protected $casts = [
        'rule_value' => 'array', // avtomatik JSON ↔️ array
    ];

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }

    /**
     * Qoidani tekshiruvchi helper.
     */
    public function passesFor(array $target): bool
    {
        $key = $this->rule_key;
        $operator = $this->rule_operator;
        $value = $this->rule_value;

        if (!array_key_exists($key, $target)) {
            return false;
        }

        $inputValue = $target[$key];

        return match ($operator) {
            '='       => $inputValue == $value[0],
            'IN'      => in_array($inputValue, $value),
            '>='      => $inputValue >= $value[0],
            'BETWEEN' => isset($value[0], $value[1]) && $inputValue >= $value[0] && $inputValue <= $value[1],
            default   => false,
        };
    }
}
