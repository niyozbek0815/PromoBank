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
        Schema::create('prize_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['manual', 'smart_random', 'auto_bind']);
            $table->text('description')->nullable();
            $table->timestamps();
            // Uncomment if soft deletes are needed
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_categories');
    }
};
