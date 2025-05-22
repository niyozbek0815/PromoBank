<?php

namespace App\Models;

use Str;
use App\Models\GameCard;
use App\Models\Leaderboard;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Game extends Model
{
    use HasTranslations;

    protected static function booted(): void
    {
        static::creating(function (Game $game) {
            if (empty($game->slug) && is_array($game->name) && isset($game->name['en'])) {
                $game->slug = \Illuminate\Support\Str::slug($game->name['en']);
            }
        });
    }

    protected $fillable = [
        'name',
        'title',
        'about',
        'slug',
        'stage1_card_count',
        'stage2_card_count',
    ];

    public $translatable = [
        'name',
        'title',
        'about',
    ];
    public function leaderboards()
    {
        return $this->hasMany(Leaderboard::class);
    }


    public function cards()
    {
        return $this->hasMany(GameCard::class);
    }
    public function sessions()
    {
        return $this->hasMany(GameSession::class);
    }
}
