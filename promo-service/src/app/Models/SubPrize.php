<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubPrize extends Model
{
    protected $fillable = [
        'prize_id',
        'value',
    ];

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }
}
