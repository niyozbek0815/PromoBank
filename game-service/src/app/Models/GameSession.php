<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    protected $fillable = [
        'game_id',
        'user_id',
        'status',
        'total_score',
        'stage1_score',
        'stage2_score',
        'stage1_success_steps',
        'stage2_attempted',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }



    // public function logs()
    // {
    //     return $this->hasMany(GameLog::class);
    // }

    // public function result()
    // {
    //     return $this->hasOne(GameResult::class);
    // }

    public function stage1Results()
    {
        return $this->hasMany(GameStage1Result::class, 'session_id');
    }

    public function stage2Result()
    {
        return $this->hasOne(GameStage2Result::class, 'session_id');
    }

    public function sessionCards()
    {
        return $this->hasMany(GameSessionCard::class, 'session_id');
    }
}
