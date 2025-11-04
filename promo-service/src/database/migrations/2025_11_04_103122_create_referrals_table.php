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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            // 🔹 Taklif qilgan foydalanuvchi
            $table->unsignedBigInteger('referrer_id')->nullable();

            // 🔹 Taklif qilingan foydalanuvchi
            $table->unsignedBigInteger('referred_user_id')->nullable();
            // 🔹 Telegram identifikatorlari
            $table->string('referrer_chat_id')->index();
            $table->string('referred_chat_id')->nullable()->index();
            $table->string('referred_username', 255)->nullable();

            // 🔹 Holat — tizim bosqichlari
            $table->enum('status', [
                'started',     // /start bosilgan
                'registered',  // ro‘yxatdan o‘tgan
                'activated',   // bonus berilgan
            ])->default('started')->index();
            $table->unsignedBigInteger('awarded_points')->default(0);

            $table->timestamps();
            $table->unique(['referrer_id', 'referred_chat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
