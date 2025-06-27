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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('phone', 50)->unique();
            $table->string('phone2', 50)->nullable();
            $table->string('chat_id', 50)->unique();
            $table->char('gender', 1)->nullable(); // M, F, U
            $table->date('birthdate')->nullable();
            $table->string('lang', 2)->nullable(); // uz, ru, kr
            $table->boolean('is_guest')->default(false);
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('device', 50); // mobile, desktop, telegram
            $table->string('device_model', 50);
            $table->string('platform', 50); // Android, iOS, Telegram
            $table->longText('payload');
            $table->string('user_agent')->nullable();
            $table->integer('last_activity')->index();
            $table->timestamp('otp_sent_at')->nullable(); // OTP yuborilgan vaqt
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};