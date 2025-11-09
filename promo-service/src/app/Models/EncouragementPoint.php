<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EncouragementPoint extends Model
{
    protected $table = 'encouragement_points';

    protected $fillable = [
        'user_id',
        'scope_type',  // morph type
        'scope_id',    // morph id
        'type',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Type helpers
     */
    public function user()
    {
        return $this->belongsTo(UsersCache::class, 'user_id', 'user_id');
    }
    public function isScanner(): bool
    {
        return $this->type === 'scanner';
    }

    public function isGame(): bool
    {
        return $this->type === 'game';
    }

    public function isReferral(): bool
    {
        return in_array($this->type, ['referral_start', 'referral_register']);
    }

    /**
     * Morph relation generic accessor
     */
    public function scope(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Specific receipt morph relation (if needed)
     */
    public function receipt(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'scope_type', 'scope_id');
    }

    /**
     * Auto-set user_id from morph relation if missing
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (!$model->user_id && $model->scope && property_exists($model->scope, 'user_id')) {
                $model->user_id = $model->scope->user_id;
            }
        });
    }
}
