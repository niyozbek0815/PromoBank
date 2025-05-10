<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesProduct extends Model
{
    protected $fillable = [
        'receipt_id',
        'name',
        'count',
        'summa',
    ];

    public function receipt()
    {
        return $this->belongsTo(SalesReceipt::class, 'receipt_id');
    }
}
