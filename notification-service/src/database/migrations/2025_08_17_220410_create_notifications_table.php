<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
      public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // multi-lang
            $table->json('text');  // multi-lang
            $table->enum('target_type', ['platform','users','excel']);
            $table->enum('link_type', ['game','promotion','url','message']);
            $table->string('link')->nullable(); // id or url
            $table->enum('status', ['draft','scheduled','processing','sent','failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedBigInteger('total_recipients')->default(0);
            $table->unsignedBigInteger('sent_count')->default(0);
            $table->unsignedBigInteger('failed_count')->default(0);
            $table->unsignedBigInteger('pending_count')->default(0);
            $table->json('meta')->nullable(); // e.g. created_by, campaign_id, priority
            $table->timestamps();
            $table->softDeletes(); // deleted_at
            $table->index(['status','scheduled_at']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
