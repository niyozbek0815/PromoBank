<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        // Bu yerda profilni yangilash uchun kerakli logikani qo'shing
        // Masalan, foydalanuvchi ma'lumotlarini yangilash, rasm yuklash va h.k.

        // Misol uchun, foydalanuvchi ma'lumotlarini olish va qaytarish
        // $user = $request->user();

        return view('admin.profile.update', );
    }
}
