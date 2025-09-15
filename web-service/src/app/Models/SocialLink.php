<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SocialLink extends Model
{
    use HasTranslations;

    protected $fillable = ['type', 'url', 'label', 'position', 'status'];

    public $translatable = ['label'];
}
