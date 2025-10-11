<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameStage2Result extends Model
{
    protected $fillable = [
        'session_id',
        'stage2_played',
        'final_score',
        'revealed_card_ids'
    ];

    protected $casts = [
        'stage2_played' => 'boolean',
        'revealed_card_ids' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(GameSession::class);
    }
}
