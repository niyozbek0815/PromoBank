<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameCard extends Model
{
    protected $fillable = [
        'game_id',
        'point',
        'stage',
        'frequency',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
    public function sessionCards()
    {
        return $this->hasMany(GameSessionCard::class, 'card_id');
    }
}
