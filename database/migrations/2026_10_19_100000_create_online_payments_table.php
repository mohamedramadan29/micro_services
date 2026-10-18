<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every payment on the site goes through one EasyKash account with one
 * callback URL, so each one gets a row here: its id is the EasyKash
 * customerReference, and `purpose` says what to do once it is paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('purpose', 30);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('EGP');
            $table->string('status', 20)->default('pending');
            $table->json('payload')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_mobile', 30)->nullable();
            $table->string('return_token', 64);
            $table->string('gateway', 20)->default('easykash');
            $table->string('gateway_transaction')->nullable();
            $table->decimal('paid_amount', 12, 2)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['purpose', 'status']);
        });

        Schema::table('request_payments', function (Blueprint $table) {
            $table->foreignId('online_payment_id')->nullable()->after('id')->constrained('online_payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('request_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('online_payment_id');
        });
        Schema::dropIfExists('online_payments');
    }
};
