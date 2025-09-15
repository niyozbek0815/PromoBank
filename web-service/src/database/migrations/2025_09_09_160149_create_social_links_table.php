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
            Schema::create('social_links', function (Blueprint $table) {
                $table->id();
                $table->string('type', 50);
                $table->string('url', 1024);
                $table->json('label')->nullable(); // ko‘p tilli label
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
        Schema::dropIfExists('social_links');
    }
};
