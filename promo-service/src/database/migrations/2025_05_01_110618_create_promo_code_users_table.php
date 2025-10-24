<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo_code_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')
                ->nullable()
                ->constrained('promo_codes')
                ->onDelete('cascade');

            $table->foreignId('user_id');
            $table->foreignId('promotion_id')->nullable()->constrained()->onDelete('set null');

            $table->foreignId('receipt_id')->nullable();

            // platform_id -> CASCADE
            $table->foreignId('platform_id')
                ->constrained()
                ->onDelete('cascade');

            // promotion_product_id -> SET NULL
            $table->foreignId('promotion_product_id')
                ->nullable()
                ->constrained('promotion_products')
                ->onDelete('set null');

            // prize_id -> SET NULL (cascade + set null bo‘lib ketgan edi, tuzatdim)
            $table->foreignId('prize_id')
                ->nullable()
                ->constrained('prizes')
                ->onDelete('set null');

            $table->string('sub_prize_id')->nullable();

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
