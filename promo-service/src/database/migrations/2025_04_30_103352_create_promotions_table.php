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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->json('name');
            $table->json('title');
            $table->json('description');
            $table->boolean('status')->default(false);
            $table->boolean('is_public')->default(false);
            $table->enum('winning_strategy', ['immediate', 'delayed', 'hybrid'])->default('immediate');
            $table->json('code_settings')->nullable();
                                                          // $table->enum('winner_selection_type', ['manual', 'random', 'criteria'])->default('manual');
            $table->json('extra_conditions')->nullable(); // e.g., {"min_age":18,"location":"Tashkent"}
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
