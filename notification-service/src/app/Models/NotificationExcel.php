<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasMedia;

class NotificationExcel extends Model
{
        use  HasMedia;

      protected $fillable = ['notification_id', 'total_rows', 'processed_rows'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
       public function getExcelAttribute()
    {
        return $this->getFirstMediaUrl('notification-excel') ?: null;
    }
}
