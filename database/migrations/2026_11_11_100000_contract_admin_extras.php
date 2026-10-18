<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3 audit gaps: the team asks both sides for a reply by a date (outside a dispute),
 * and every change of «سياسة المشاريع» is logged with who and when.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_reply_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('question');
            $table->timestamp('due_at');
            $table->text('client_reply')->nullable();
            $table->timestamp('client_replied_at')->nullable();
            $table->text('freelancer_reply')->nullable();
            $table->timestamp('freelancer_replied_at')->nullable();
            $table->timestamp('overdue_alerted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('contract_policy_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('changes'); // {key: [before, after]}
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_policy_changes');
        Schema::dropIfExists('contract_reply_requests');
    }
};
