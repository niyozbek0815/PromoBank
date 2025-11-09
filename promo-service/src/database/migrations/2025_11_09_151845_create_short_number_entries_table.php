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
        Schema::create('short_number_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_number_id')->constrained('short_numbers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('points_awarded')->default(0); // foydalanuvchiga berilgan ball
            $table->string('user_input', 20);              // foydalanuvchi yuborgan raqam
            $table->boolean('is_accepted')->default(false); // qabul qilindi/yo'q
            $table->timestamps();
            $table->unique(['short_number_id', 'user_id']); // bir foydalanuvchi bir raqamni bir marta yuboradi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_number_entries');
    }
};
