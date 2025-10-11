<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasMedia;

    protected $fillable = ['key_name', 'val', 'status'];

    protected $appends = ['image'];

    /**
     * Logo uchun maxsus keylar
     */
    protected const LOGO_KEYS = ['navbar_logo', 'footer_logo'];

    public static function logoKeys(): array
    {
        return self::LOGO_KEYS;
    }

    public function getImageAttribute(): ?string
    {
        return optional($this->getMedia('logo'))['url'] ?? null;
    }

    /**
     * Val accessor → har doim array/string to‘g‘ri qaytaradi
     */
    public function getValAttribute($value)
    {
        // JSON decode
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $val = $decoded ?? $value;

        // Agar logo bo‘lsa — image url qaytaramiz
        if (in_array($this->key_name, self::LOGO_KEYS, true)) {
            return $this->image ?? $val;
        }

        return $val;
    }

    /**
     * Val mutator → array bo‘lsa JSON qilib saqlanadi
     */
    public function setValAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['val'] = json_encode($value, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['val'] = $value;
        }
    }
}
