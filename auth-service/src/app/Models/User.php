<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasMedia;

    /**
     * Mass assignable fields
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'phone2',
        'region_id',
        'district_id',
        'gender',
        "status",
        "is_guest"
    ];

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
    ];

    // JWT interface methods
    public function getJWTIdentifier()
    {
        return $this->getKey(); // returns the user id
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'user_id' => $this->id,
            'phone' => $this->phone,
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