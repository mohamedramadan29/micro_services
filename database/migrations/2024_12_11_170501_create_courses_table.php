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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('image');
            $table->longText('desc');
            $table->text(column: 'adv')->nullable();
            $table->text(column: 'learn')->nullable()->comment('ماذا سوف تتعلم ');
            $table->text('terms_course')->nullable()->comment('شروط الدخول');
            $table->double('course_hourse')->nullable();
            $table->integer('leason_numbers');
            $table->double('price');
            $table->integer('student_num')->nullable();
            $table->integer('current_student_num')->default(0);
            $table->date('start_date')->nullable();
            $table->date(column: 'end_date')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
