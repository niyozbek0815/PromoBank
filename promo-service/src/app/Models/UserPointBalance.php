<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPointBalance extends Model
{
    protected $primaryKey = 'user_id';
    // public $incrementing = false;

    protected $fillable = [
        'user_id',
        'balance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function encouragementPoints(): HasMany
    {
        return $this->hasMany(EncouragementPoint::class, 'user_id', 'user_id');
    }
}
