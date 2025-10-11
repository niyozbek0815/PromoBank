<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    protected $fillable = [
        'notification_id',
        'phone',
        'user_id',
        'device_id',
        'token',
        'status',
        'attempt_count',
        'last_attempt_at',
        'last_error',
        'meta',
    ];

    protected $casts = [
        'status' => 'string',
        'meta' => 'array',
        'last_attempt_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
