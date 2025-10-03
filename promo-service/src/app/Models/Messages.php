<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $fillable = [
        'scope_type',
        'scope_id',
        'type',
        'status',
        'message',
    ];

    protected $casts = [
        'message' => 'array', // JSON ni avtomatik array qilib beradi
    ];

    // ENUM qiymatlarni const sifatida
    public const SCOPES = ['platform', 'promotion', 'prize'];
    public const TYPES = ['promo', 'receipt'];
    public const STATUSES = ['claim', 'pending', 'invalid', 'win', 'lose', 'fail'];

    public function scopeable()
    {
        return $this->morphTo(__FUNCTION__, 'scope_type', 'scope_id');
    }

    public function scopePlatform($query)
    {
        return $query->where('scope_type', 'platform');
    }

    public function scopePromotion($query, $promotionId)
    {
        return $query->where('scope_type', 'promotion')
            ->where('scope_id', $promotionId);
    }

    public function scopePrize($query, $prizeId)
    {
        return $query->where('scope_type', 'prize')
            ->where('scope_id', $prizeId);
    }
//     $message = Message::prize($prizeId)
//     ->where('type', 'promo')
//     ->where('status', 'win')
//     ->first()
//     ?? Message::promotion($promotionId)
//         ->where('type', 'promo')
//         ->where('status', 'win')
//         ->first()
//     ?? Message::platform()
//         ->where('type', 'promo')
//         ->where('status', 'win')
//         ->first();

// if ($message) {
//     $lang = app()->getLocale(); // masalan 'uz', 'ru', 'en', 'qq'
//     $text = $message->message[$lang] ?? $message->message['uz']; // fallback
// }
}
