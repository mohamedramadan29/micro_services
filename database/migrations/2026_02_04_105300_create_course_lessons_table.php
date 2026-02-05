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
        Schema::create('new_course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_course_topic_id')->references('id')->on('new_course_topics')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url')->comment('YouTube unlisted video URL');
            $table->string('video_id')->nullable()->comment('YouTube video ID extracted from URL');
            $table->integer('duration_minutes')->nullable()->comment('Video duration in minutes');
            $table->boolean('is_free')->default(false)->comment('Free preview or paid');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_course_lessons');
    }
};
