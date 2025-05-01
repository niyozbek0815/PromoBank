<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeMessage extends Model
{
    protected $fillable = [
        'prize_id',
        'platform',
        'message_type',
        'message',
    ];

    protected $casts = [
        'platform' => 'string',
        'message_type' => 'string',
        'message' => 'string',
    ];

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }
}
