<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersCache extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'status',
    ];

}
