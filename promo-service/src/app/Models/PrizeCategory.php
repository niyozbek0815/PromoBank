<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeCategory extends Model
{

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
    ];
    public function prizePromos()
    {
        return $this->hasMany(PrizePromo::class, 'category_id');
    }
    public function prizes()
    {
        return $this->hasMany(Prize::class, 'category_id')->orderBy('index', 'asc');
    }
}
