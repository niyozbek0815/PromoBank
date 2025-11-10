<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecretNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'points',
        'promotion_id',
        'start_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotions::class);
    }

    public function entries()
    {
        return $this->hasMany(SecretNumberEntry::class);
    }
}
