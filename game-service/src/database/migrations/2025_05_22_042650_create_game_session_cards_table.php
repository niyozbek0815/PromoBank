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
        Schema::create('game_session_cards', function (Blueprint $table) {
            $table->id();
            // FK: game_sessions.id
            $table->foreignId('session_id')->constrained('game_sessions')->cascadeOnDelete();
            // FK: game_cards.id
            $table->foreignId('card_id')->constrained('game_cards')->cascadeOnDelete();
            // Step number: only used for stage1 (1 through 5)
            $table->tinyInteger('step_number')->nullable();
            // Whether the card is revealed to user (opened visually)
            $table->boolean('is_revealed')->default(false);
            // Whether this card was a success (matched target value)
            $table->boolean('is_success')->default(false);
            // Whether the user selected this card or it was shown automatically
            $table->boolean('selected_by_user')->default(false);
            $table->integer('etap')->default(1); // 1 or 2

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_session_cards');
    }
};
