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
        Schema::create('prize_promos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('prize_id')->constrained()->onDelete(action: 'cascade');
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('prize_categories')
                ->nullOnDelete();
            $table->foreignId('promo_code_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sub_prize')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_promos');
    }
};
