<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Conversation between a client and the site team about one request
     * (questions about prices, missing details…). Who spoke last drives the
     * "asking" / "answering" client classifications.
     */
    public function up(): void
    {
        Schema::create('request_admin_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('from_admin');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['service_request_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_admin_messages');
    }
};
