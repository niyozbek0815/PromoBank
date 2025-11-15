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
        Schema::create('ontv_vauchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ontv_vauchers');
    }
};
