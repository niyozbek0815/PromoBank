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
        Schema::create('promo_code_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_codes_id')->constrained('promo_codes')->onDelete('cascade');
            $table->foreignId('user_id');
            $table->foreignId('receipt_id')->nullable();
            $table->foreignId('platform_id')->constrained()->onDelete('cascade');
            $table->foreignId('promotion_product_id')->nullable()->constrained('promotion_products')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_code_users');
    }
};
