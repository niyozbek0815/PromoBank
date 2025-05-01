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
        Schema::create('promotion_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('length');
            $table->string('charset')->default('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
            $table->string('exclude_chars')->nullable();
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->boolean('unique_across_all_promotions')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_settings');
    }
};
