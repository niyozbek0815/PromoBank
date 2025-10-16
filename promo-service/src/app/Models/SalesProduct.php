<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesProduct extends Model
{
    protected $table = 'sales_products';

    protected $fillable = [
        'receipt_id',
        'name',
        'count',
        'summa',
    ];

    protected $casts = [
        'count' => 'decimal:3', // 3 xonali aniqlik
        'summa' => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(SalesReceipt::class, 'receipt_id');
    }
}
