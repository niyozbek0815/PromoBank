<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
    ];

    public function setStartAtAttribute($value)
    {
        // input qaysi formatda kelishidan qat'iy nazar, Tashkent vaqtiga parse qilamiz
        $this->attributes['start_at'] = Carbon::parse($value, 'Asia/Tashkent');
    }
    public function getStartAtAttribute($value)
    {
        // Log::info($this->asDateTime($value)->setTimezone(config('app.timezone')) );
        return $value ? $this->asDateTime($value)->setTimezone(config('app.timezone')) : null;
    }



    public function promotion()
    {
        return $this->belongsTo(Promotions::class);
    }

    public function entries()
    {
        return $this->hasMany(SecretNumberEntry::class);
    }
}
