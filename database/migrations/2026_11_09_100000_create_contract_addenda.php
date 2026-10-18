<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3: an addendum after the work started (add a stage, change an unsettled stage's
 * amount, duration, revisions or deliverable, remove an unfunded stage) — proposed by one
 * side, applied only when the other approves.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_addenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('proposed'); // proposed | approved | rejected | cancelled
            $table->json('changes');                // [{type: add|change|remove, stage_id?, title?, deliverable?, acceptance?, duration_days?, amount?, revisions?}]
            $table->text('reason');
            $table->decimal('total_before', 12, 2);
            $table->decimal('total_after', 12, 2);
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('response_note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::table('contract_stages', function (Blueprint $table) {
            $table->foreignId('contract_addendum_id')->nullable()->constrained('contract_addenda')->nullOnDelete(); // added by an addendum
        });
    }

    public function down(): void
    {
        Schema::table('contract_stages', fn (Blueprint $table) => $table->dropConstrainedForeignId('contract_addendum_id'));
        Schema::dropIfExists('contract_addenda');
    }
};
