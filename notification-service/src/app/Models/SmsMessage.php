<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    protected $fillable = [
        'phone',
        'message',
        'status',
        'sent_at',
        'retry_count',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
