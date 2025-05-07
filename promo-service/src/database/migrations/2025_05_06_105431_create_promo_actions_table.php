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

            $table->enum('action', [
                'claim',        // kod ishlatilgan
                'edit',         // admin o‘zgartirdi
                'vote',         // foydalanuvchi ovoz berdi
                'block',        // bloklandi
                'manual_add',   // admin yutuq berdi
                'auto_win',     // avtomatik yutuq (smart_random)
            ]);

            $table->enum('status', [
                'pending',   // tekshirilmoqda
                'won',       // yutdi
                'failed',    // yutolmadi
                'blocked',   // bloklandi
                'confirmed', // tasdiqlandi
                'canceled',  // bekor qilindi
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