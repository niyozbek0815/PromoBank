<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadLink extends Model
{
    protected $fillable = ['download_id', 'type', 'url', 'label', 'position', 'status'];
    protected $casts = [
        'label' => 'array',
    ];

    public function download()
    {
        return $this->belongsTo(Download::class);
    }
}
