<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Whether each notification reached its channel (bell / email) or failed — site check spec page 17 #8. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notification', 120);   // class basename
            $table->string('channel', 30);         // database | mail
            $table->string('status', 10);          // sent | failed
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->string('recipient')->nullable(); // name or email
            $table->string('text', 500)->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
