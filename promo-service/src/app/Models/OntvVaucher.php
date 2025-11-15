<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class OntvVaucher extends Model
{
    use HasFactory;

    protected $table = 'ontv_vauchers';

    protected $fillable = [
        'code',
        'user_id',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Voucher foydalanuvchiga berilganmi?
     */
    public function isAssigned(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Voucher ishlatilganmi?
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Voucher hali amal qiladimi (expired emas va ishlatilmagan)
     */
    public function isValid(): bool
    {
        $now = Carbon::now();
        return !$this->isUsed() && (!$this->expires_at || $this->expires_at->gte($now));
    }

    /**
     * Voucherni foydalanuvchiga bir marta berish
     */
    public function assignToUser(int $userId): bool
    {
        if ($this->isAssigned() || $this->isUsed()) {
            return false;
        }

        $this->user_id = $userId;
        return $this->save();
    }

    /**
     * Voucherni ishlatish
     */
    public function markAsUsed(): bool
    {
        if ($this->isUsed()) {
            return false;
        }

        $this->used_at = Carbon::now();
        return $this->save();
    }
    public function user()
    {
        return $this->belongsTo(UsersCache::class, 'user_id', 'user_id');
    }
}
