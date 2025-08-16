<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Banner extends Model
{
    use SoftDeletes, HasTranslations;

    protected $fillable = [
        'title',
        'media',
        'url',
        'banner_type',
        'status',
        'created_by',
    ];

    // 🔹 Translatable maydonlar
    public array $translatable = ['title'];

    // 🔹 Casting
    protected $casts = [
        'title'  => 'array',
        'media'  => 'array', // {uz: {url, mime_type}, ru: {...}, kr: {...}}
        'status' => 'boolean',
    ];

    // 🔹 Banner active scope
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // 🔹 Banner kim tomonidan yaratilgan (agar users jadvali bo‘lsa)
    // public function creator()
    // {
    //     return $this->belongsTo(User::class, 'created_by');
    // }
}
