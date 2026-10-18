<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Landing pages built from sections in the admin (client file «تعديلات», item 07). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug', 80)->unique();
            $table->string('meta_description', 300)->nullable();
            $table->foreignId('og_image_id')->nullable()->constrained('content_media')->nullOnDelete();
            $table->boolean('show_site_nav')->default(true);
            $table->string('header_button_text', 60)->nullable();
            $table->string('header_button_link', 500)->nullable();
            $table->json('content')->nullable(); // published sections
            $table->json('draft')->nullable();   // unpublished edits of the sections (null = same as published)
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
