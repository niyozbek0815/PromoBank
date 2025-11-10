<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SecretNumberEntry extends Model
{
    use HasFactory;

    protected $table = 'secret_number_entries';

    protected $fillable = [
        'secret_number_id',
        'user_id',
        'points_awarded',
        'user_input',
        'is_accepted',
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
        'points_awarded' => 'integer',
    ];

    /**
     * Relatsiya: Entry qaysi SecretNumberga tegishli
     */
    public function secretNumber()
    {
        return $this->belongsTo(SecretNumber::class);
    }

    /**
     * Relatsiya: Entry qaysi foydalanuvchiga tegishli
     */
    public function user()
    {
        return $this->belongsTo(UsersCache::class, 'user_id', 'user_id');
    }

    /**
     * Scope: faqat qabul qilingan entry lar
     */
    // public function scopeAccepted(Builder $query)
    // {
    //     return $query->where('is_accepted', true);
    // }

    // /**
    //  * Scope: qabul qilinmagan entry lar
    //  */
    // public function scopePending(Builder $query)
    // {
    //     return $query->where('is_accepted', false);
    // }

    // /**
    //  * Entry ni qabul qilish va ball berish
    //  */
    // public function accept(int $points = null)
    // {
    //     $this->is_accepted = true;

    //     if (!is_null($points)) {
    //         $this->points_awarded = $points;
    //     }

    //     return $this->save();
    // }

    // /**
    //  * Entry ni bekor qilish / rad etish
    //  */
    // public function reject()
    // {
    //     $this->is_accepted = false;
    //     $this->points_awarded = 0;
    //     return $this->save();
    // }
}
