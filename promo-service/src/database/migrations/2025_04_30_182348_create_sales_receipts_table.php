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
        Schema::create('sales_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');

            $table->string('name');
            $table->string('chek_id')->unique();
            $table->string('nkm_number');
            $table->string('sn'); // serial number

            $table->timestamp('check_date');
            $table->enum('payment_type', ['naqt', 'karta']);
            $table->decimal('qqs_summa', 12, 2);
            $table->decimal('summa', 12, 2);

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('long', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_receipts');
    }
};