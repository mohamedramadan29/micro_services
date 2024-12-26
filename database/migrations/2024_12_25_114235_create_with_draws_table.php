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
        Schema::create('with_draws', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->double('amount');
            $table->string('method');
            $table->string('paypal_mail');
            $table->tinyInteger('status')->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('with_draws');
    }
};
