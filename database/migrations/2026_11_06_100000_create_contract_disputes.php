<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3, phase 5 (client decisions 2026-10-16): disputes (3 days to respond, money frozen,
 * the admin decides with a written reason) and changing the freelancer (always through the admin;
 * the leaving freelancer gets an admin-set % of the unapproved stage; the rest is re-offered).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_stage_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 30);
            $table->text('description');
            $table->string('status', 20)->default('open'); // open | awaiting_decision | decided | withdrawn
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('respond_due_at')->useCurrent();
            $table->string('decision', 30)->nullable();
            $table->text('decision_note')->nullable();
            $table->unsignedTinyInteger('freelancer_percent')->nullable(); // split
            $table->date('revision_due')->nullable();                      // «revision» decision
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contract_freelancer_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 20);                       // client | withdrawal | admin
            $table->text('reason');
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->foreignId('leaving_freelancer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('leaving_percent')->nullable(); // of the current unapproved stage
            $table->text('decision_note')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('continuation_request_id')->nullable()->constrained('service_requests')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('contract_delivery_files', function (Blueprint $table) {
            $table->foreignId('contract_dispute_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('service_requests', function (Blueprint $table) {
            // A continuation after a change of freelancer points to the contract it continues.
            $table->foreignId('continues_contract_id')->nullable()->constrained('contracts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', fn (Blueprint $table) => $table->dropConstrainedForeignId('continues_contract_id'));
        Schema::table('contract_delivery_files', fn (Blueprint $table) => $table->dropConstrainedForeignId('contract_dispute_id'));
        Schema::dropIfExists('contract_freelancer_changes');
        Schema::dropIfExists('contract_disputes');
    }
};
