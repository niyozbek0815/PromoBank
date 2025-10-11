<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameStage1Step extends Model
{
    protected $fillable = [
        'game_id',
        'step_number',
        'card_count',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
