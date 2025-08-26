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
        Schema::create('notification_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->string('phone')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();   // optional
            $table->unsignedBigInteger('device_id')->nullable()->index(); // FK to user_devices.id (nullable if phone-only)
            $table->string('token')->nullable()->index();                 // snapshot of token at send time
            $table->enum('status', ['pending', 'processing', 'not_registered', 'sent', 'viewed', 'failed'])->default('pending');
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('meta')->nullable(); // e.g. chosen channel, payload used
            $table->timestamps();
            $table->index(['notification_id', 'status']);
            $table->index(['notification_id', 'attempt_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_users');
    }
};
