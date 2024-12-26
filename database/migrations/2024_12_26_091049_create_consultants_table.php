<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('specialization');
            $table->text('bio');
            $table->string('image')->nullable();
            $table->float('rating')->default(0);
            $table->boolean('is_active')->default(true);
            $table->decimal('price', 8, 2)->nullable();
            $table->string('aviable_date')->nullable();
            $table->string('aviable_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultants');
    }
};
