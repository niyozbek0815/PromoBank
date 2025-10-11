<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipationType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function promotions()
    {
        return $this->belongsToMany(Promotions::class, 'promotion_participation_types')
            ->withPivot(['is_enabled', 'additional_rules'])
            ->withTimestamps();
    }
}
