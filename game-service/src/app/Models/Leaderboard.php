<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    protected $fillable = [
        'game_id',
        'user_id',
        'score',
        'rank',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
