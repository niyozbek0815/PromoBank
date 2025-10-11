<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPlatform extends Model
{
        protected $fillable = ['notification_id', 'platform'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
