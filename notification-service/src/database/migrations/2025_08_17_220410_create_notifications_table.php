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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // { "uz": "Sarlavha", "ru": "...", "kr": "..." }
            $table->json('text');  // { "uz": "Matn", ... }
            $table->enum('target_type', ['platform', 'users', 'excel']);
            $table->enum('link_type', ['game', 'promotion', 'url', 'message']);
            $table->string('link')->nullable(); // id yoki url
            $table->enum('status', ['draft', 'scheduled', 'sent', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
