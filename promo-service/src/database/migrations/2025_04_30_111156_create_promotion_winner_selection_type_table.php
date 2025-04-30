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
        Schema::create('promotion_winner_selection_type', function (Blueprint $table) {
            $table->foreignId('promotions_id')->constrained()->onDelete('cascade');
            $table->foreignId('winner_selection_type_id')->constrained()->onDelete('cascade');
            $table->primary(['promotions_id', 'winner_selection_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_winner_selection_type');
    }
};
