<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Traits\HasMedia;


class Portfolio extends Model
{
    use SoftDeletes, HasTranslations, HasMedia;

    protected $fillable = ['title', 'subtitle', 'body', 'image', 'is_featured', 'position', 'status'];

    public $translatable = ['title', 'subtitle', 'body'];

    protected $appends = ['image'];
    public function getImageAttribute()
    {
        $media = $this->getMedia('portfolio');
        if (!empty($media['url'])) {
            return $media['url'];
        }

        return $this->attributes['image'] ?? null;    }
}
