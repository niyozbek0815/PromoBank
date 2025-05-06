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
        Schema::create('smart_random_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // masalan: code_length, starts_with
            $table->string('label');         // admin uchun ko‘rinadigan nom
            $table->string('input_type');    // masalan: text, number, select, multiselect
            $table->boolean('is_comparison')->default(true);
            // true => ishlatiladigan comparison operatorlar bo‘ladi (=, !=, >=, ...)
            // false => faqat `IN` yoki `NOT IN` kabi ishlatiladi

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_random_rules');
    }
};
