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
        Schema::create('properity_maintains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('description');
            $table->string('category');
            $table->string('contract_type');
            $table->double('area', 8, 2)->nullable();
            $table->integer('rooms')->nullable();
            $table->string('location');
            $table->string('city');
            $table->string('country');
            $table->string('service_type');
            $table->tinyInteger('active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properity_maintains');
    }
};
