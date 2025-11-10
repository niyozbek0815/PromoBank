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
        'channel',
        'message',
    ];

    /** ENUM qiymatlar */
    public const SCOPES = ['platform', 'promotion', 'prize'];
    public const TYPES = ['promo', 'receipt','secret-number'];
    public const STATUSES = ['claim', 'pending', 'invalid', 'win', 'lose', 'fail', 'step0', 'step1', 'step3', 'step_won'];
    public const CHANNELS = ['telegram', 'sms', 'mobile', 'web'];

    public function scopeable()
    {
        return $this->morphTo(__FUNCTION__, 'scope_type', 'scope_id');
    }

    public function scopePlatform($query)
    {
        return $query->where('scope_type', 'platform');
    }

    public function scopePromotion($query, int $promotionId)
    {
        return $query->where('scope_type', 'promotion')->where('scope_id', $promotionId);
    }

    public function scopePrize($query, int $prizeId)
    {
        return $query->where('scope_type', 'prize')->where('scope_id', $prizeId);
    }

    public function setMessageAttribute($value): void
    {
        $channel = $this->attributes['channel'] ?? $this->channel ?? 'mobile';

        // 1️⃣ SMS uchun — oddiy text saqlanadi
        if ($channel === 'sms') {
            $this->attributes['message'] = is_string($value)
                ? trim($value)
                : json_encode($value, JSON_UNESCAPED_UNICODE);
            return;
        }

        // 2️⃣ Boshqa kanallar (telegram, mobile, web) — doimo JSON saqlanadi
        if (is_array($value)) {
            // To‘g‘ridan to‘g‘ri JSON format
            $this->attributes['message'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } elseif (is_string($value)) {
            // String bo‘lsa — default til "uz" uchun o‘raymiz
            $this->attributes['message'] = json_encode(['uz' => trim($value)], JSON_UNESCAPED_UNICODE);
        } else {
            // Fallback: bo‘sh massiv
            $this->attributes['message'] = json_encode(['uz' => ''], JSON_UNESCAPED_UNICODE);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR — MESSAGE O‘QISH
    |--------------------------------------------------------------------------
    */
    public function getMessageAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        $firstChar = substr($trimmed, 0, 1);

        // Oddiy text (SMS)
        if ($firstChar !== '{' && $firstChar !== '[') {
            return $trimmed;
        }

        // Decode JSON
        $decoded = json_decode($trimmed, true);

        return json_last_error() === JSON_ERROR_NONE
            ? $decoded
            : $trimmed;
    }
    /**
     * Lokalizatsiyalangan xabarni topish
     */
    public static function resolveLocalizedMessage(string $type, array $data): ?string
    {
        $status = $data['status'] ?? null;
        $lang = $data['lang'] ?? app()->getLocale();
        $channel = $data['channel'] ?? 'mobile';
        $prizeId = $data['prize_id'] ?? null;
        $promoId = $data['promotion_id'] ?? null;

        if (!$status) {
            return null;
        }

        $query = static::query()
            ->where('type', $type)
            ->where('status', $status)
            ->where('channel', $channel);

        $message = null;

        if ($prizeId) {
            $message = (clone $query)->prize($prizeId)->first();
        }

        if (!$message && $promoId) {
            $message = (clone $query)->promotion($promoId)->first();
        }

        if (!$message) {
            $message = (clone $query)->platform()->first();
        }

        if (!$message) {
            return null;
        }

        $msg = $message->message;

        // Oddiy text (telegram/sms)
        if (is_string($msg)) {
            return $msg;
        }

        // Multi-lang (mobile/web)
        return $msg[$lang] ?? $msg['uz'] ?? reset($msg);
    }
}
