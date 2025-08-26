<?php
namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, Notifiable, HasTranslations, HasMedia, SoftDeletes;

  protected $fillable = [
    'title',
    'text',
    'target_type',
    'link_type',
    'link',
    'status',
    'scheduled_at',
    'total_recipients',
    'sent_count',
    'failed_count',
    'pending_count',
    'meta',
];

  protected $casts = [
    'title'        => 'array',
    'text'         => 'array',
    'meta'         => 'array',
    'scheduled_at' => 'datetime',
];

    protected $appends = ['image'];
    public $translatable = ['text', 'title'];

public function getImageAttribute()
{
    $media = $this->getMedia('notification-image');
    return $media['url'] ?? null;
}

    public function notificationwiew()
    {
        return $this->hasMany(NotificationWiew::class, 'notification_id');
    }
    public function platforms()
    {
        return $this->hasMany(NotificationPlatform::class);
    }

    public function users()
    {
        return $this->hasMany(NotificationUser::class);
    }

    public function excel()
    {
        return $this->hasOne(NotificationExcel::class);
    }
}
