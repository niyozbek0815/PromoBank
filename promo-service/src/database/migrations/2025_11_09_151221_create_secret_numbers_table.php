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

        Schema::create('secret_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('number', 10); // Qisqa raqam
            $table->integer('points')->nullable();   // Userga beriladigan ball
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->dateTime('start_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secret_numbers');
    }
};
