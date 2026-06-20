<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained('email_contacts')->onDelete('set null');
            $table->string('email');
            $table->string('name')->nullable();
            $table->json('custom_fields')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced', 'unsubscribed'])->default('pending');
            $table->uuid('tracking_token')->unique()->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->integer('opened_count')->default(0);
            $table->timestamp('clicked_at')->nullable();
            $table->integer('click_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
    }
};
