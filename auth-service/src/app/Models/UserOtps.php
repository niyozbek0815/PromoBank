<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOtps extends Model
{
    use HasFactory;

    // Agar kerakli bo'lsa, qo'llaniladigan jadval nomini belgilash (agar jadval nomi model nomidan farq qilsa)
    protected $table = 'user_otps';

    // Model uchun fillable yoki guarded ni belgilash (oshirib kiritilgan ma'lumotlarni oldini olish uchun)
    protected $fillable = [
        'user_id',
        'phone',
        'otp',
        'token',
        'otp_sent_at',
        'expires_at',
        'used',
    ];

    // Yoki, xavfsizlik uchun, protected $guardedni ishlatishingiz mumkin (agar barcha atributlarni xavfsiz saqlamoqchi bo'lsangiz)
    // protected $guarded = [];

    // Eslatma: Eslatma sifatida, modeldagi maydonlarni saqlashda vaqtni formatlashni amalga oshirish ham mumkin
    protected $dates = ['otp_sent_at', 'expires_at'];
}
