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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->enum('scope_type', ['platform', 'promotion', 'prize']);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->enum('type', ['promo', 'receipt']);
            $table->enum('status', ['claim', 'pending', 'invalid', 'win', 'lose','fail']);
            $table->json('message'); // { "uz": "...", "ru": "...", "en": "...", "qq": "..." }
            $table->timestamps();
            $table->index(['scope_type', 'scope_id', 'type', 'status'], 'message_scope_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
