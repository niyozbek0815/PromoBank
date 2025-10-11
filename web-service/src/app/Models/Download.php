<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Traits\HasMedia;

class Download extends Model
{
    use  HasTranslations, HasMedia;

    protected $fillable = ['title', 'subtitle', 'description', 'image', 'status'];
    protected $casts = [
        'title' => 'array',
        'subtitle' => 'array',
        'description' => 'array',
    ];
    protected $appends = ['image'];
    public function getImageAttribute()
    {
        $media = $this->getMedia('download');

        if (!empty($media['url'])) {
            return $media['url'];
        }

        return $this->attributes['image'] ?? null;
    }

    public function links()
    {
        return $this->hasMany(DownloadLink::class);
    }
}
