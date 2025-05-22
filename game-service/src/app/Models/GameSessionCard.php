<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSessionCard extends Model
{
    protected $fillable = [
        'session_id',
        'card_id',
        'step_number',
        'is_revealed',
        'is_success',
        'selected_by_user',
    ];

    public function session()
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    public function card()
    {
        return $this->belongsTo(GameCard::class, 'card_id');
    }
}
