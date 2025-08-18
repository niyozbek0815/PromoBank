<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Translatable\HasTranslations;

class Notification extends Model
{
        use  HasFactory, Notifiable, HasTranslations, HasMedia;

    protected $fillable = ['type', 'link_type', 'link', 'text', 'title'];
    public $translatable = ['text', 'title'];

    public function getImageAttribute()
    {
return $this->getFirstMediaUrl('notification-image') ?: null;
    }

    public function notificationwiew()
    {
        return $this->hasMany(NotificationWiew::class, 'notification_id');
    }
}
