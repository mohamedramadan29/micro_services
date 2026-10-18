<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A freelancer's offer on a request: one per freelancer per request.
        Schema::create('request_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->unsignedSmallInteger('duration_days');
            $table->text('scope');            // نطاق العمل والتسليمات
            $table->text('message');          // رسالة العرض
            $table->json('portfolio_links')->nullable();
            $table->string('status')->default('new'); // new | viewed | in_discussion | accepted | rejected | withdrawn
            $table->boolean('is_saved')->default(false); // shortlisted by the client
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['service_request_id', 'freelancer_id']);
        });

        Schema::create('request_offer_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_offer_id')->constrained('request_offers')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        // Conversation tied to one offer on one request.
        Schema::create('offer_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('request_offer_id')->constrained('request_offers')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['request_offer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_messages');
        Schema::dropIfExists('request_offer_files');
        Schema::dropIfExists('request_offers');
    }
};
