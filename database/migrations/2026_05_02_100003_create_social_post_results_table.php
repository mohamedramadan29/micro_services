<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_post_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('social_posts')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('social_accounts')->onDelete('cascade');
            $table->enum('platform', ['facebook', 'instagram', 'tiktok', 'youtube', 'twitter', 'linkedin']);
            $table->string('platform_post_id')->nullable(); // ID البوست على المنصة
            $table->enum('status', ['pending', 'published', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->datetime('published_at')->nullable();
            $table->json('engagement')->nullable(); // likes, comments, shares, views
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_results');
    }
};
