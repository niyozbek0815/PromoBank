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
        Schema::create('encouragement_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('receipt_id')->nullable()->constrained('sales_receipts')->nullOnDelete();
            $table->integer('points');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encouragement_points');
    }
};
