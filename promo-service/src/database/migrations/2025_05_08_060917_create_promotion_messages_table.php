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
        Schema::create('promotion_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete(); // default message if null
            $table->enum('platform', ['sms', 'mobile', 'bot', 'all']);
            $table->enum('message_type', ['success', 'fail', 'claim', 'info', 'etc']);
            $table->json('message'); // laravel translatableda olindigan qilib  uz ru va kr xolatda
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_messages');
    }
};
