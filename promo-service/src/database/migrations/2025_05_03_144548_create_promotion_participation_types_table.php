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
        Schema::create('promotion_participation_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('participation_type_id')->constrained()->onDelete('cascade');
            $table->boolean('is_enabled')->default(false);
            $table->json('additional_rules')->nullable(); // optional: limit, constraints
            $table->timestamps();
            $table->unique(['promotion_id', 'participation_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_participation_types');
    }
};
