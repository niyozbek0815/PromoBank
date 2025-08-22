<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
        protected $fillable = ['notification_id', 'phone', 'status', 'error_message'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
