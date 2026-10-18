<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editing a request (client decision 2026-10-16): free edits before an offer is
 * chosen; after work starts a change request the freelancer approves, with the
 * price difference paid (increase) or returned to the client's balance (decrease).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->timestamp('edited_at')->nullable();          // last edit before an offer was chosen
            $table->decimal('agreed_price', 12, 2)->nullable();  // price after approved changes (null = the chosen offer's)
        });

        Schema::table('request_offers', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable();       // last time the freelancer sent/updated the offer
        });

        Schema::create('service_request_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('freelancer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description');                          // what changes in the work
            $table->text('reason')->nullable();
            $table->date('old_deadline')->nullable();
            $table->date('new_deadline')->nullable();
            $table->decimal('old_price', 12, 2);
            $table->decimal('new_price', 12, 2);
            $table->string('status', 20)->default('pending');    // pending | awaiting_payment | applied | rejected | cancelled
            $table->text('response_note')->nullable();           // the freelancer's reason when rejecting
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
            $table->index(['service_request_id', 'status']);
        });

        Schema::table('request_payments', function (Blueprint $table) {
            $table->foreignId('service_request_change_id')->nullable()->constrained()->nullOnDelete(); // a price-difference payment
            $table->decimal('refunded_amount', 12, 2)->default(0); // given back to the client's balance after a price decrease
        });
    }

    public function down(): void
    {
        Schema::table('request_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_request_change_id');
            $table->dropColumn('refunded_amount');
        });
        Schema::dropIfExists('service_request_changes');
        Schema::table('request_offers', fn (Blueprint $table) => $table->dropColumn('submitted_at'));
        Schema::table('service_requests', fn (Blueprint $table) => $table->dropColumn(['edited_at', 'agreed_price']));
    }
};
