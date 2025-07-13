<?php
namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, HasMedia;

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
    protected $appends   = ['logo'];
    protected $casts     = [
        'settings' => 'array',
    ];
    public function getLogoAttribute()
    {
        return $this->getMedia('logo'); // yoki 'avatar'
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