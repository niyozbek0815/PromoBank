<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationWiew extends Model
{

    protected $fillable=['notification_id','user_id',  'status'];



    public function userNotificationAlls()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
}
