<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3 «مشروعك ما بيوقف» — the money side (client decisions 2026-10-15): a staged
 * contract; each stage is funded (site balance or EasyKash, several ahead if the client
 * wants), «يبقى المبلغ لدى نفّذها حتى تعتمد المرحلة», then released to the freelancer minus
 * the commission, refunded to the client's site balance, or split.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->nullable()->unique();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('freelancer_id')->nullable()->constrained('users')->nullOnDelete(); // current freelancer
            $table->string('title');
            $table->string('status', 20)->default('active'); // active | completed | cancelled
            $table->string('currency', 3)->default('EGP');
            $table->timestamps();
        });

        Schema::create('contract_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('unfunded'); // unfunded | funding | funded | released | refunded | split
            $table->string('funding_source', 20)->nullable();  // balance | easykash
            $table->foreignId('online_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('commission_rate', 5, 2)->nullable(); // fixed when funded
            $table->foreignId('released_to')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('released_amount', 12, 2)->default(0);   // the freelancer's part before commission
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('freelancer_amount', 12, 2)->default(0);
            $table->decimal('refunded_amount', 12, 2)->default(0);   // back to the client's site balance
            $table->text('decision_note')->nullable();               // why it was refunded / split
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('settled_at')->nullable();             // released, refunded or split
            $table->timestamps();
            $table->index(['contract_id', 'sort_order']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('contract_stage_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('settings', function (Blueprint $table) {
            // EasyKash fees are paid by the client (decision 2026-10-15), added on top of a stage paid by EasyKash.
            $table->decimal('stage_fee_percent', 5, 2)->default(0);
            $table->decimal('stage_fee_fixed', 8, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('settings', fn (Blueprint $table) => $table->dropColumn(['stage_fee_percent', 'stage_fee_fixed']));
        Schema::table('wallet_transactions', fn (Blueprint $table) => $table->dropConstrainedForeignId('contract_stage_id'));
        Schema::dropIfExists('contract_stages');
        Schema::dropIfExists('contracts');
    }
};
