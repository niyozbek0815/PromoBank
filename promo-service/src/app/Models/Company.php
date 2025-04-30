<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'user_id',
        'name',
        'title',
        'description',
        'email',
        'settings',
        'status',
        'region',
        'address',
        'contact_person',
        'created_by_user_id',
    ];
    public $translatable = ['name', 'title', 'description'];
    protected $casts = [
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function socialMedia()
    {
        return $this->hasMany(SocialMedia::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotions::class);
    }
}
