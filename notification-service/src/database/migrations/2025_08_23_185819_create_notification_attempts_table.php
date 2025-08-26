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
        Schema::create('notification_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_user_id')->constrained('notification_users')->cascadeOnDelete();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->enum('channel', ['fcm', 'apns', 'web', 'telegram', 'sms', 'email'])->default('fcm');
            $table->enum('result', ['success', 'failed', 'retryable_failed', 'not_registered'])->index();
            $table->text('error_message')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('latency_ms')->nullable();
            $table->timestamps();
            $table->index(['notification_id', 'result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_attempts');
    }
};
