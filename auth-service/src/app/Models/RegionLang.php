<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionLang extends Model
{
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
    ];

    protected $casts = [
        'name' => 'array',
    ];
}
