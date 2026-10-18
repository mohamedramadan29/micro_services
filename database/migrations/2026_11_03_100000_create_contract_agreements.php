<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3, phase 2: the agreement. The freelancer proposes (scope, client materials,
 * stages with deliverable/duration/amount/revisions), the client approves or asks for
 * changes; every version is kept with who approved it and when. Approval opens the stages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version');
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('proposed'); // proposed | changes_requested | approved | superseded
            $table->text('scope');                  // what is included
            $table->text('out_of_scope')->nullable(); // what is not
            $table->text('client_materials')->nullable(); // what the client must send (files, texts, access…)
            $table->json('stages');                 // [{title, deliverable, duration_days, amount, revisions|null}]
            $table->decimal('total', 12, 2);
            $table->text('freelancer_note')->nullable();
            $table->text('client_note')->nullable(); // the client's requested changes
            $table->timestamp('freelancer_approved_at')->nullable(); // proposing = the freelancer's approval
            $table->timestamp('client_approved_at')->nullable();
            $table->string('client_approved_ip', 45)->nullable();
            $table->timestamps();
            $table->unique(['contract_id', 'version']);
        });

        Schema::table('contract_stages', function (Blueprint $table) {
            $table->foreignId('contract_agreement_id')->nullable()->constrained()->nullOnDelete();
            $table->text('deliverable')->nullable();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->unsignedSmallInteger('revisions')->nullable(); // null = unlimited
        });
    }

    public function down(): void
    {
        Schema::table('contract_stages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contract_agreement_id');
            $table->dropColumn(['deliverable', 'duration_days', 'revisions']);
        });
        Schema::dropIfExists('contract_agreements');
    }
};
