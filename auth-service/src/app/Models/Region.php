<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';

    protected $fillable = ['name'];
    public function district()
    {
        return $this->hasMany(District::class);
    }
}