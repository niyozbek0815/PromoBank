<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promo_actions', function (Blueprint $table) {
            $table->id();

            // 🔗 Bog‘lanishlar
            $table->foreignId('promotion_id')->nullable()->constrained();            $table->foreignId('promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('prize_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();

            $table->foreignId('shop_id')
                ->nullable()
                ->constrained('promotion_shops')
                ->nullOnDelete();

            // 🧾 Harakat bog‘langan chek (agar mavjud bo‘lsa)
            $table->foreignId('receipt_id')
                ->nullable()
                ->constrained('sales_receipts')
                ->nullOnDelete();

            // 🔄 Harakat turi — foydalanuvchi yoki tizim tomonidan bajarilgan amallar
            $table->enum('action', [
                'claim',        // ➜ Promokod ishlatilgan (foydalanuvchi tomonidan)
                'edit',         // ➜ Admin tomonidan o‘zgartirish kiritilgan
                'vote',         // ➜ Foydalanuvchi ovoz berish yoki ishtirok harakati
                'block',        // ➜ Harakat yoki foydalanuvchi bloklangan
                'manual_add',   // ➜ Admin tomonidan sovg‘a yoki bonus qo‘lda berilgan
                'auto_win',     // ➜ Promokod avtomatik tarzda yutishga sabab bo‘lgan (auto_bind)
                'smart_win',    // ➜ Smart algoritm orqali yutish (smart_random strategiya)
                'manual_win',   // ➜ Qo‘lda topshirilishi kerak bo‘lgan sovg‘a (pending holatda)
                'weighted_win', // ➜ Ehtimollik asosida yutish (weighted_random strategiya)
                'points_win',   // ➜ Promobal (bonus ball) yutish yoki olish holati
                'no_win',       // ➜ Yutolmadi — ishtirok muvaffaqiyatsiz yakunlandi
                'points_win'
            ]);

            // 📊 Holat — amaldagi jarayonning natijaviy statusi
            $table->enum('status', [
                'pending',            // ➜ Jarayon kutilmoqda yoki tekshirilmoqda
                'blocked',            // ➜ Harakat to‘xtatilgan yoki foydalanuvchi bloklangan
                'confirmed',          // ➜ Harakat tasdiqlangan (muvaffaqiyatli yakun)
                'canceled',
                         // ➜ Jarayon bekor qilingan
                'scaner',

                // Promokod orqali ishlov jarayonlari:
                'promocode_claim',    // ➜ Promokod allaqachon ishlatilgan
                'promocode_pending',  // ➜ Promokod tekshirilmoqda
                'promocode_invalid',  // ➜ Promokod noto‘g‘ri yoki mavjud emas
                'promocode_win',      // ➜ Promokod orqali yutish holati
                'promocode_fail',     // ➜ Promokod jarayoni xatolik bilan yakunlandi
                'promocode_lose',     // ➜ Promokod yutolmadi (ishtirok muvaffaqiyatsiz)

                'scaner_win',
                'scaner_pending',
                'scaner_fail',
                'scaner_invalid',


                // SMS orqali ishlov jarayonlari:
                'sms_claim',          // ➜ SMS kod allaqachon ishlatilgan
                'sms_pending',        // ➜ SMS tekshirilmoqda
                'sms_invalid',        // ➜ SMS noto‘g‘ri yoki mavjud emas
                'sms_win',            // ➜ SMS orqali yutish holati
                'sms_fail',           // ➜ SMS ishlovda tizim xatosi
                'sms_lose',           // ➜ SMS orqali yutolmadi (ishtirok muvaffaqiyatsiz)
            ])->nullable();

            // 🕓 Amal bajarilgan vaqt (foydalanuvchi tomonidan yoki tizim orqali)
            $table->timestamp('attempt_time')->nullable();

            // 🧾 Harakat haqida tizim xabari yoki foydalanuvchiga ko‘rsatilgan matn
            $table->text('message')->nullable();

            $table->timestamps();

            // ⚡️ Performance uchun indekslar
            $table->index(['user_id', 'promotion_id']);
            $table->index(['receipt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_actions');
    }
};
