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
            $table->id(); // Primary key
            $table->string('chek_id')->unique(); // Unikal check raqami
            $table->string('name')->nullable(); // Xaridor ismi (agar mavjud bo‘lsa)
            $table->string('address')->nullable(); // Xaridor ismi (agar mavjud bo‘lsa)
            $table->string('nkm_number'); // NKM raqami
            $table->string('sn'); // Seriya raqami
            $table->timestamp('check_date'); // Check vaqti
            $table->decimal('qqs_summa', 12, 2); // QQS summasi
            $table->decimal('summa', 12, 2); // Umumiy summa
            $table->decimal('lat', 10, 7); // GPS latitude
            $table->decimal('long', 10, 7); // GPS longitude
            $table->string('payment_type')->nullable();
            $table->foreignId('user_id'); // Kim yuklagan
            $table->timestamps(); // created_at va updated_at
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
