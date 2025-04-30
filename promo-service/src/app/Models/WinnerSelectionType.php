<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinnerSelectionType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function promotions()
    {
        return $this->belongsToMany(Promotions::class, 'promotion_winner_selection_type');
    }
}
