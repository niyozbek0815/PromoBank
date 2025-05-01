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
        Schema::create('promo_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('promo_code_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id');
            $table->foreignId('prize_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('action', ['yutdi', 'ovoz berdi', 'edit', 'block']);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_logs');
    }
};
