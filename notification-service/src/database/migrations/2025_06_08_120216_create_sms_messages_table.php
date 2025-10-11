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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20); // +998901234567 format uchun yetarli
            $table->text('message');     // yuborilgan matn
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending'); // yuborish holati
            $table->timestamp('sent_at')->nullable(); // yuborilgan vaqt
            $table->unsignedInteger('retry_count')->default(0); // necha marta qayta uringan
            $table->text('error_message')->nullable(); // yuborishda xatolik bo‘lsa
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
