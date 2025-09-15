<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Traits\HasMedia;


class Sponsor extends Model
{
    use SoftDeletes, HasTranslations, HasMedia;

    protected $fillable = ['name', 'url', 'media_id', 'weight', 'status', 'image'];

    public $translatable = ['name'];
    protected $casts = [
        'name' => 'array',
    ];
    protected $appends = ['image'];
    public function getImageAttribute()
    {
        $media = $this->getMedia('sponsor');

        if (!empty($media['url'])) {
            return $media['url'];
        }

        return $this->attributes['image'] ?? null;
    }
}
