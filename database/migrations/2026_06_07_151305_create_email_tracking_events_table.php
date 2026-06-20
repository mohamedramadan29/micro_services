<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('email_campaign_recipients')->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained('email_contacts')->onDelete('set null');
            $table->enum('event_type', ['open', 'click', 'bounce', 'unsubscribe']);
            $table->text('url')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_tracking_events');
    }
};
