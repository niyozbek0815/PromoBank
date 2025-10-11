<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('prize_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('platform_id')
                ->constrained()
                ->onDelete('cascade');
            $table->enum('action', [
                'claim',        // kod ishlatilgan
                'edit',         // admin o‘zgartirdi
                'vote',         // foydalanuvchi ovoz berdi
                'block',        // bloklandi
                'manual_add',   // admin yutuq berdi
                'auto_win',     // avtomatik prize bog'langan promo yutuq (auto_bind)
                'smart_win', // smart yutuq (smart_random)
                'manual_win', // Qo'lda topshiladigan sovga uchun imkoniyat (manual_win)
                'no_win' //Yuruq yutilmadi
            ]);

            $table->enum('status', [
                'pending',   // tekshirilmoqda
                'blocked',   // bloklandi
                'confirmed', // tasdiqlandi
                'canceled',  // bekor qilindi
                'promocode_claim',   // allaqachon foydalanilgan (kod yoki chek avval ishlatilgan)
                'promocode_pending', // qabul qilingan, natija kutilmoqda
                'promocode_invalid', // noto‘g‘ri yoki mavjud bo‘lmagan kod/chek
                'promocode_win',     // foydalanuvchi yutgan holat
                'promocode_fail',    // tizim xatosi yoki ro‘yxatdan o‘tolmadi
                'promocode_lose',    // yutolmadi, urinish muvaffaqiyatsiz tugadi
            ])->nullable();

            $table->timestamp('attempt_time')->nullable();
            $table->text('message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_actions');
    }
};
