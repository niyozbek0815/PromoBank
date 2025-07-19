<?php
namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasMedia, HasRoles;

    /**
     * Mass assignable fields
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'phone2',
        'chat_id',
        'region_id',
        'district_id',
        'gender',
        'birthdate',
        "status",
        "is_guest",
        "lang",
    ];
    protected $appends = ['avatar'];
    /**
     * Hidden fields during serialization (like API responses)
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Type casting for specific fields
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate'         => 'date:Y-m-d',
    ];

    // JWT interface methods
    public function getJWTIdentifier()
    {
        return $this->getKey(); // returns the user id
    }
    public function getAvatarAttribute(): ?string
    {
        return $this->getMedia('user_avatar'); // yoki 'avatar'
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'user_id'  => $this->id,
            'phone'    => $this->phone,
            'is_guest' => $this->is_guest,
        ];
    }
    public function userOtps()
    {
        return $this->hasOne(UserOtps::class, 'user_id', 'id')->where('used', false)->latest();
    }
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}