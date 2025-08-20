<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $table = 'user_devices';

    protected $fillable = [
        'user_id',
        'ip_address',
        'is_guest',
        'fcm_token',
        'device_type',
        'device_name',
        'app_version',
        'phone',
        'user_agent',
        'last_activity',
    ];

    protected $casts = [
        'is_guest'      => 'boolean',
        'last_activity' => 'integer',
    ];

}
