<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameStage1Result extends Model
{

    protected $fillable = [
        'session_id',
        'step_number',
        'target_point',
        'success',
        'revealed_card_ids',
        'created_at'
    ];

    protected $casts = [
        'success' => 'boolean',
        'revealed_card_ids' => 'array',
        'created_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(GameSession::class);
    }
}
