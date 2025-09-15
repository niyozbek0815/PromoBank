<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'uuid',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'path',
        'url'
    ];

    public function model()
    {
        return $this->morphTo();
    }
    public function getFullUrlAttribute()
    {
        $baseUrl = config('services.urls.api_getaway');
        return $baseUrl  . $this->url;
    }
}
