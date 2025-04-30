<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialType extends Model
{
    protected $table = 'social_types';

    protected $fillable = [
        'name',
    ];

    public function socialMedia()
    {
        return $this->hasMany(SocialMedia::class, 'type_id');
    }
}