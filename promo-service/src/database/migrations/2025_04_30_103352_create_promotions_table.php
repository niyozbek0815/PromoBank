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
            // $table->enum('platform', ['telegram', 'web', 'mobile', 'all'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->json('code_settings')->nullable(); // e.g., {"length":6,"charset":"alnum"}
            // $table->enum('winner_selection_type', ['manual', 'random', 'criteria'])->default('manual');
            $table->json('extra_conditions')->nullable(); // e.g., {"min_age":18,"location":"Tashkent"}
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->string('status')->default('draft'); // 'draft', 'active', 'finished', etc.
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
