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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Qamrov sohasi — xabar qaysi darajada amal qiladi
            $table->enum('scope_type', ['platform', 'promotion', 'prize'])
                ->comment('Xabar qamrovi: platforma, kampaniya yoki mukofot darajasi');

            $table->unsignedBigInteger('scope_id')
                ->nullable()
                ->comment('Tegishli obyekt ID (promotion_id yoki prize_id)');

            // Xabar turi
            $table->enum('type', ['promo', 'receipt'])
                ->comment('Xabar turi: promo kod yoki chek uchun');

            // Holat
            $table->enum('status', ['claim', 'pending', 'invalid', 'win', 'lose', 'fail'])
                ->comment('Xabar holati: foydalanilgan, kutyapti, noto‘g‘ri, yutdi, yutqazdi yoki xato');

            // Kanal
            $table->enum('channel', ['telegram', 'sms', 'mobile', 'web'])
                ->default('mobile')
                ->comment('Xabar kanali: Telegram, SMS yoki mobil ilova orqali');

            // Xabar matni — JSON yoki oddiy text bo‘lishi mumkin
            $table->text('message')
                ->comment('Ko‘p tilli yoki oddiy xabar. JSON shaklda (mobile/web) yoki oddiy text (telegram/sms).');

            $table->timestamps();

            $table->index(['scope_type', 'scope_id', 'type', 'status', 'channel'], 'message_scope_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
