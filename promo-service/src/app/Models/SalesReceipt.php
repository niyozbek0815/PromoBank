<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReceipt extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'chek_id',
        'nkm_number',
        'sn',
        'check_date',
        'payment_type',
        'qqs_summa',
        'summa',
        'lat',
        'long',
    ];
    public function products()
    {
        return $this->hasMany(SalesProduct::class, 'receipt_id');
    }
}