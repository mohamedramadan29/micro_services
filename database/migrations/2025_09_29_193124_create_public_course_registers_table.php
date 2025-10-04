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
        Schema::create('public_course_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_course_id')->references('id')->on('public_courses')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('certificate');
            $table->string('country');
            $table->string('city');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_course_registers');
    }
};
