<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3, phase 6 (client decisions 2026-10-16): staff roles (manager / dispute reviewer /
 * accountant, money decisions above a manager-set amount need the manager), ratings after a
 * project (client → freelancer public, freelancer → client for freelancers and the admin only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('admin_role', 20)->nullable();          // null = manager (every admin before roles)
            $table->unsignedInteger('rating_count')->default(0);   // ratings behind users.rate (freelancers)
            $table->decimal('client_rating', 3, 2)->nullable();    // freelancers' ratings of this client
            $table->unsignedInteger('client_rating_count')->default(0);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('contract_approval_threshold', 12, 2)->default(0); // 0 = no manager approval needed
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rating_reminder_sent_at')->nullable();
        });

        Schema::create('contract_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rater_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ratee_id')->constrained('users')->cascadeOnDelete();
            $table->string('direction', 30); // client_to_freelancer | freelancer_to_client
            $table->unsignedTinyInteger('stars');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['contract_id', 'rater_id']);
        });

        // A money decision waiting for approval (reviewer → accountant/manager, or above the threshold → manager).
        Schema::table('contract_disputes', function (Blueprint $table) {
            $table->json('proposed')->nullable();
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::table('contract_freelancer_changes', function (Blueprint $table) {
            $table->json('proposed')->nullable();
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contract_freelancer_changes', fn (Blueprint $table) => [$table->dropConstrainedForeignId('proposed_by'), $table->dropColumn('proposed')]);
        Schema::table('contract_disputes', fn (Blueprint $table) => [$table->dropConstrainedForeignId('proposed_by'), $table->dropColumn('proposed')]);
        Schema::dropIfExists('contract_ratings');
        Schema::table('contracts', fn (Blueprint $table) => $table->dropColumn(['completed_at', 'rating_reminder_sent_at']));
        Schema::table('settings', fn (Blueprint $table) => $table->dropColumn('contract_approval_threshold'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['admin_role', 'rating_count', 'client_rating', 'client_rating_count']));
    }
};
