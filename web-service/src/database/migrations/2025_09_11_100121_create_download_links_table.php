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
        Schema::create('download_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('download_id')->constrained('downloads')->cascadeOnDelete();
            $table->string('type', 50);        // googleplay, appstore, telegram
            $table->string('url', 1024);
            $table->json('label')->nullable(); // agar keyinchalik custom nomlar kerak bo‘lsa
            $table->integer('position')->default(0)->index();
            $table->tinyInteger('status')->default(1)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_links');
    }
};
