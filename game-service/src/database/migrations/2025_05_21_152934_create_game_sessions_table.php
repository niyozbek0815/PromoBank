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
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->uuid('user_id'); // Auth service-dan kelgan UUID
            $table->enum('status', [
                'in_progress',
                'stage1_complete',
                'stage2_played',
                'finished'
            ])->default('in_progress');
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedInteger('stage1_score')->default(0);
            $table->unsignedInteger('stage2_score')->default(0);
            $table->tinyInteger('stage1_success_steps')->default(0); // 0-5
            $table->boolean('stage2_attempted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};