<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content');
            $table->enum('media_type', ['text', 'image', 'video', 'reel', 'story'])->default('text');
            $table->json('media_paths')->nullable();      // مسارات الميديا
            $table->json('platforms');                    // ['facebook','instagram','tiktok']
            $table->enum('status', ['draft', 'scheduled', 'publishing', 'published', 'failed', 'partial'])->default('draft');
            $table->datetime('scheduled_at')->nullable(); // وقت النشر المجدول
            $table->datetime('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
