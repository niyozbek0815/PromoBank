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
        Schema::create('platform_promotion', function (Blueprint $table) {
            $table->foreignId('platform_id')->constrained()->onDelete('cascade');
            $table->foreignId('promotions_id')->constrained()->onDelete('cascade');
            $table->primary(['platform_id', 'promotions_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_promotion');
    }
};
