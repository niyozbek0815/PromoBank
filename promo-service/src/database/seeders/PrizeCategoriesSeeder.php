<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PrizeCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'name'         => 'manual',
                'display_name' => 'Qoʻlda tanlash',
                'description'  => "Ushbu turdagi yutuqlar faqat administrator tomonidan tanlab beriladi.
    Ishtirokchining muvaffaqiyati tizim tomonidan avtomatik emas, balki
    <strong>qoʻl bilan amalga oshirilgan qaror</strong> asosida belgilanadi.
    Bu tur <em>kengaytirilgan nazorat</em> talab qiladigan hollarda,
    maxsus ishtirokchilar yoki alohida holatlar uchun qo‘llaniladi.",
                'created_at'   => $now,
                'updated_at'   => $now],
            [
                'name'         => 'smart_random',
                'display_name' => 'Aqlli tasodifiy (Smart random)',
                'description'  => "Tizim promokodlarni avtomatik ravishda oldindan belgilangan aqlli shartlar (masalan: platforma, vaqt, qatnashish soni) asosida tekshiradi va mos kelsa sovg‘ani darhol beradi. Har bir urunish individual baholanadi.",
                'created_at'   => $now,
                'updated_at'   => $now],
            [
                'name'         => 'auto_bind',
                'display_name' => 'Avto-bogʻlash',
                'description'  => "Sovg‘alar oldindan maxsus promokodlar bilan bogʻlanadi. Faqat shu maxsus kodlar orqali sovg‘a yutib olinadi. Bu modelda g‘oliblar aniq promokodlar asosida belgilanadi.",
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'name'         => 'weighted_random',
                'display_name' => 'Extimoliy tasodifiy',
                'description'  => "Har bir sovg‘a ehtimoliy (proportsional) og‘irlikka ega bo‘ladi. Foydalanuvchilar tasodifiy tanlanadi, biroq ba’zi sovg‘alarning ehtimoli yuqoriroq bo‘lishi mumkin. Har bir qatnashishda faqat bitta sovg‘ani yutib olish mumkin.",
                'created_at'   => $now,
                'updated_at'   => $now],

        ];

        DB::table('prize_categories')->insert($categories);
    }
}
