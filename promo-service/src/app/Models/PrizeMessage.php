<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PrizeMessage extends Model
{
    use HasTranslations;
    protected $fillable = [
        'prize_id',
        'platform',
        'message_type',
        'message',
    ];
    public $translatable = ['message']; // Allow message field to be translatable

    protected $casts = [
        'platform' => 'string',
        'message_type' => 'string',
        'message' => 'string',
    ];

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }
    public static function getMessageFor(Prize $prize, string $platform, string $type)
    {
        return self::where('prize_id', $prize->id)
            ->where('platform', $platform)
            ->where('message_type', $type)
            ->first();
    }
}
