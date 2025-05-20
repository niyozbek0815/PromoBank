<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncouragementPoint extends Model
{
    protected $fillable = [
        'user_id',
        'receipt_id',
        'points',
    ];



    public function receipt()
    {
        return $this->belongsTo(SalesReceipt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
