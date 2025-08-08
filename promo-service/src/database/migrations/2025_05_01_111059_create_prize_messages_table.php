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
        Schema::create('prize_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prize_id')->nullable()->constrained('prizes')->nullOnDelete(); // default message if null
            $table->enum('platform', ['sms',"all"]);
            $table->enum('participant_type', [ 'receipt_scan','smart_random','all']);
            $table->enum('message_type', ['success', 'fail', 'info', 'etc']);
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
        Schema::dropIfExists('prize_messages');
    }
};
