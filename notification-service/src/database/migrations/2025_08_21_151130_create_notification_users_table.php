<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->string('phone')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('device_id')->nullable()->index();
            $table->string('token')->nullable()->index();
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['notification_id', 'status']);
            $table->index(['notification_id', 'attempt_count']);
        });

        // CHECK constraintni alohida qo‘shamiz
        DB::statement("
            ALTER TABLE notification_users
            ADD CONSTRAINT notification_users_status_check
            CHECK (status IN ('pending','processing','not_registered','sent','viewed','failed','hidden'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_users');
    }
};
