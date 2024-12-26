<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->string('payment_method');
            $table->string('payment_status');
            $table->decimal('payment_amount', 10, 2);
            $table->decimal('payment_fee',  10, 2)->default(0);
            $table->string('payment_currency', 10)->default('USD');
            $table->string('payment_description')->nullable();
            $table->json('payment_response')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
