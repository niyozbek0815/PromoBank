<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use App\Traits\HasMedia;

class Benefit extends Model
{
    use SoftDeletes, HasTranslations, HasMedia;

    protected $fillable = ['title', 'description', 'media_id', 'position', 'status','image'];

    public $translatable = ['title', 'description'];

    protected $appends = ['image'];
    public function getImageAttribute()
    {
        $media = $this->getMedia('benefit');

        if (!empty($media['url'])) {
            return $media['url'];
        }

        return $this->attributes['image'] ?? null;
    }
}
