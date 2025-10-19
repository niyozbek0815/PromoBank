<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')
                ->constrained('sales_receipts')
                ->onDelete('cascade');

            $table->string('name');
            $table->decimal('count', 8, 3); // ✅ change() YO‘Q
            $table->decimal('summa', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_products');
    }
};
