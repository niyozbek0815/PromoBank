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
        Schema::create('smart_random_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prize_id')->constrained()->onDelete('cascade');
            $table->foreignId('rule_id')->constrained('smart_random_rules')->onDelete('cascade');

            $table->string('operator');        // Masalan: =, !=, >=, IN, NOT IN
            $table->json('values');            // Masalan: ["A", "B", "C"] yoki [6]

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_random_values');
    }
};
