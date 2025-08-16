<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

                                   // Ko‘p tilli sohalar
            $table->json('title'); // {uz: "...", ru: "...", kr: "..."}
            // Asosiy xususiyatlar
            $table->string('url')->nullable();
            $table->enum('banner_type', ['promotion', 'game', 'url','news'])->default('promotion');

            // Audit
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('banner_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
