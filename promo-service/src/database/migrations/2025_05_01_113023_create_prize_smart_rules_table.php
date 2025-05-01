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
        Schema::create('prize_smart_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prize_id')->constrained()->onDelete('cascade');
            $table->string('rule_key'); // Masalan: gender, region_id
            $table->string('rule_operator'); // Masalan: =, IN, >=, BETWEEN
            $table->json('rule_value'); // Masalan: ["male"], ["10", "11"], ["2"]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_smart_rules');
    }
};
