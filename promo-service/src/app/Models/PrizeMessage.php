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
        'participant_type',
        'message',
    ];
    public $translatable = ['message']; // Allow message field to be translatable

    protected $casts = [
        'platform' => 'string',
        'message_type' => 'string',
    ];

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }
public static function getMessageFor(int $prizeId, string $platform, string $type)
{
    return self::where('prize_id', $prizeId)
        ->where('platform', $platform)
        ->where('message_type', $type)
        ->first();
}
}
