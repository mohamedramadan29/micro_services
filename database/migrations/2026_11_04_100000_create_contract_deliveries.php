<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3, phase 3: stages run one after another. A stage starts when it is funded and the
 * previous one is approved (its deadline counts from then); the freelancer delivers, the client
 * approves (money released) or asks for a revision within the agreed number.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_stages', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->unsignedSmallInteger('revisions_used')->default(0);
            $table->timestamp('delivered_at')->nullable(); // latest delivery waiting for review
        });

        Schema::create('contract_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_stage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note');
            $table->json('links')->nullable();
            $table->string('status', 20)->default('submitted'); // submitted | approved | revision_requested
            $table->text('client_note')->nullable();            // what to revise
            $table->timestamp('review_due_at')->nullable();      // 5 days for the client (decision 2026-10-16)
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contract_delivery_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_delivery_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_delivery_files');
        Schema::dropIfExists('contract_deliveries');
        Schema::table('contract_stages', fn (Blueprint $table) => $table->dropColumn(['started_at', 'due_at', 'revisions_used', 'delivered_at']));
    }
};
