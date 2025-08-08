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
            $table->enum('name', ['manual', 'smart_random', 'auto_bind', 'weighted_random']);
            $table->string('display_name'); // Foydalanuvchiga ko‘rsatiladigan nom (UZ)
            $table->text('description');    // Kategoriya haqida to‘liq tushuntirish
            $table->timestamps();
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
