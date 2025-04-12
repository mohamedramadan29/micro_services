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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description');
            $table->longText('experience')->nullable();
            $table->longText('job_requirements')->nullable();
            $table->longText('job_benefits')->nullable();
            $table->string('nationality')->nullable();
            $table->string('sex');
            $table->string('city');
            $table->string('country');
            $table->string('address');
            $table->tinyInteger('available_work_from_another_place')->nullable();
            $table->string('language')->nullable();
            $table->double('salary')->nullable();
            $table->integer('employs_accepted')->nullable();
            $table->tinyInteger('status')->default('0');
            $table->string('type');
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
