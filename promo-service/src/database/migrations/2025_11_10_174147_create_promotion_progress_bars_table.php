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
        Schema::create('promotion_progress_bars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');

            // Kunlik ball
            $table->integer('daily_points')->default(50)->comment('Kunlik to‘plangan ball');
            $table->integer('step_0_threshold')->default(0)->comment('0-step uchun ball');

            // Steplar uchun threshold
            $table->integer('step_1_threshold')->default(10)->comment('1-step uchun ball');
            $table->integer('step_2_threshold')->default(30)->comment('2-step uchun ball');
            $table->string('day_start_at', 5)->comment('Kunlik progress boshlanish vaqti HH:MM formatda');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_progress_bars');
    }
};
