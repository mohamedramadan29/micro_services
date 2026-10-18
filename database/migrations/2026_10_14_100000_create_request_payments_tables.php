<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('accepted_offer_id')->nullable()->after('status');
            $table->timestamp('delivered_at')->nullable()->after('published_at');
            $table->text('delivery_note')->nullable()->after('delivered_at');
            $table->timestamp('completed_at')->nullable()->after('delivery_note');
        });

        // Money the client pays for an accepted offer (through EasyKash), held by the site until
        // the client confirms the work, then released to the freelancer.
        Schema::create('request_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('request_offer_id')->constrained('request_offers')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            // Commission fixed at payment time, so later setting changes don't apply.
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->nullable();
            $table->decimal('freelancer_amount', 12, 2)->nullable();
            $table->string('status')->default('pending'); // pending | paid | released | refunded | conflict | cancelled
            $table->string('gateway')->default('easykash');
            // Our reference sent to the gateway (EasyKash customerReference) and the gateway's own transaction id.
            $table->string('gateway_reference')->nullable()->unique();
            $table->string('gateway_transaction')->nullable();
            $table->string('refund_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        // Ledger of every balance change made by the request system.
        // user_id null = the platform (commission).
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('request_payment_id')->nullable()->constrained('request_payments')->nullOnDelete();
            $table->string('type'); // escrow_in | release | commission | refund
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('request_payments');
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['accepted_offer_id', 'delivered_at', 'delivery_note', 'completed_at']);
        });
    }
};
