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
        Schema::create('encouragement_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            // Morph relation uchun faqat bitta nullableMorphs qo‘shish
            $table->nullableMorphs('scope'); // ✅ bu yerda scope_id + scope_type avtomatik yaratiladi

            $table->enum('type', ['scanner', 'game', 'referral_start', 'referral_register'])
                ->default('scanner')
                ->index();
            $table->unsignedBigInteger('points');
            $table->timestamps();
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encouragement_points');
    }
};
