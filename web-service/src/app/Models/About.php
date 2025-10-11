<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class About extends Model
{
    use  HasMedia;

    protected $fillable = [
        'subtitle',
        'title',
        'description',
        'list',
        'status',
        'image'
    ];

    protected $casts = [
        'subtitle'    => 'array',
        'title'       => 'array',
        'description' => 'array',
        'list'        => 'array',
    ];

    protected $appends = ['image'];
    public function getImageAttribute()
    {
        $media = $this->getMedia('about');

        if (!empty($media['url'])) {
            return $media['url'];
        }

        return $this->attributes['image'] ?? null;
    }
}
