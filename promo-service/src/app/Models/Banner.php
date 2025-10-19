<?php
namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Banner extends Model
{
    use SoftDeletes, HasMedia, HasTranslations;

    protected $fillable = [
        'title',
        'url',
        'banner_type',
        'status',
        'created_by',
    ];

    // 🔹 Translatable maydonlar
    public array $translatable = ['title'];

    // 🔹 Casting
    protected $casts = [
        'title' => 'array',
        'status' => 'boolean',
    ];

    protected $appends = [
        'banners_uz',
        'banners_ru',
        'banners_kr',
        'banners_en'
    ];

    // 🔹 Banner active scope
    // public function scopeActive($query)
    // {
    //     return $query->where('status', true);
    // }

    public function getBannersUzAttribute()
    {
        return $this->getMedia('banners_uz');
    }

    public function getBannersRuAttribute()
    {
        return $this->getMedia('banners_ru');
    }

    public function getBannersKrAttribute()
    {
        return $this->getMedia('banners_kr');
    }
    public function getBannersEnAttribute()
    {
        return $this->getMedia('banners_en');
    }

    // 🔹 Banner kim tomonidan yaratilgan (agar users jadvali bo‘lsa)
    // public function creator()
    // {
    //     return $this->belongsTo(User::class, 'created_by');
    // }
}
