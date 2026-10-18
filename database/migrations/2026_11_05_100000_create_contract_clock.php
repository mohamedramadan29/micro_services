<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3, phase 4 (client decisions 2026-10-16): «waiting for the client» clock pauses
 * (max 7 days, reminder at 3), freelancer lateness (reminder the day before, alert when late,
 * admin at 7 late days) and the client's 5-day review (reminder at 3, admin at 5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_pauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('request');                       // exactly what the freelancer needs
            $table->string('status', 20)->default('active'); // active | resumed | objected
            $table->text('client_response')->nullable();
            $table->text('objection')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('extended_minutes')->default(0); // added to the stage deadline
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();
        });

        Schema::table('contract_stages', function (Blueprint $table) {
            $table->timestamp('due_reminder_sent_at')->nullable();
            $table->timestamp('late_notified_at')->nullable();
            $table->timestamp('late_escalated_at')->nullable();
        });

        Schema::table('contract_deliveries', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
        });

        // Files the client sends when answering a pause share the private contract files table.
        Schema::table('contract_delivery_files', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_delivery_id')->nullable()->change();
            $table->foreignId('contract_pause_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contract_delivery_files', fn (Blueprint $table) => $table->dropConstrainedForeignId('contract_pause_id'));
        Schema::table('contract_deliveries', fn (Blueprint $table) => $table->dropColumn(['reminder_sent_at', 'escalated_at']));
        Schema::table('contract_stages', fn (Blueprint $table) => $table->dropColumn(['due_reminder_sent_at', 'late_notified_at', 'late_escalated_at']));
        Schema::dropIfExists('contract_pauses');
    }
};
