<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PromotionMessage extends Model
{
    use HasTranslations;
    protected $fillable = [
        'promotion_id',
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
    public function promotion()
    {
        return $this->belongsTo(Promotions::class, 'promotion_id');
    }
    public static function getMessageFor(Promotions $promotion, string $platform, string $type): ?PromotionMessage
    {
        return self::where('promotion_id', $promotion->id)
            ->where('platform', $platform)
            ->where('message_type', $type)
            ->first();
    }
    public static function getMessageForPromotionId(int $promotionId, string $platform, string $type): ?PromotionMessage
    {
        return self::where('promotion_id', $promotionId)
            ->where('platform', $platform)
            ->where('message_type', $type)
            ->first();
    }
}
