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
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            // $table->foreignId('type_id')->constrained('prize_types')->onDelete('restrict');
            $table->foreignId('category_id')->constrained('prize_categories')->onDelete('restrict');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('daily_limit')->nullable();
            $table->unsignedInteger('awarded_quantity')->default(0);
            $table->unsignedInteger('probability_weight')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};
