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
    Schema::create('user_devices', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable(); // user service ID
        $table->string('ip_address', 45)->nullable();
        $table->boolean('is_guest')->default(false);      // guest flag
        $table->string('fcm_token', 255)->unique();
        $table->enum('device_type', ['android', 'ios', 'web','telegram','sms']);
        $table->string('device_name', 100)->nullable(); // Redmi, iPhone va h.k.
        $table->string('app_version', 50)->nullable();
        $table->string('phone', 30)->nullable();
        $table->string('user_agent')->nullable();
        $table->integer('last_activity')->index();
        $table->timestamps();
        $table->index(['user_id', 'device_type'], 'idx_user_device');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
