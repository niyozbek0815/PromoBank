<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $guarded = [];

    protected $appends = ['full_url'];

    public function model()
    {
        return $this->morphTo();
    }

    public function getFullUrlAttribute()
    {
        $baseUrl = config('services.urls.api_getaway');
        return $baseUrl . $this->url;
    }
}
