<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_promo_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('default_points')->default(0); // foydalanuvchi har doim oladigan promobal
            $table->json('win_message');     // multi-til, yutgan paytdagi tabrik
            $table->timestamps();
        });
        DB::table('platform_promo_settings')->insert([
            'default_points' => 1,
            'win_message' => json_encode([
                        'uz' => 'Siz :promo promobal oldingiz. Yana skanerlang va yig‘ishda davom eting!',
                        'ru' => 'Вы получили :promo промобаллов. Продолжайте сканировать!',
                        'en' => 'You won :promo promoballs. Keep scanning!',
                        'kr' => 'Siz :promo promoballarni oldingiz. Skanningizni davom eting!'
                    ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_promo_settings');
    }
};
