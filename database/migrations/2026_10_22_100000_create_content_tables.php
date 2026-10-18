<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client file «تعديلات», item 06: site texts, links, images and backgrounds
 * managed from the admin. A block belongs to an area defined in
 * App\Services\Content\ContentAreas; `data` is what visitors see, `draft`
 * holds unpublished edits (null = none) until the area is published.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('area', 60);
            $table->json('data')->nullable();  // published fields (+ _visible, _order); null = never published
            $table->json('draft')->nullable(); // unpublished edits; {"_deleted": true} = delete on publish
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('area');
        });

        Schema::create('content_media', function (Blueprint $table) {
            $table->id();
            $table->string('path');          // relative to public/
            $table->string('original_name')->nullable();
            $table->string('alt')->nullable();
            $table->string('kind', 20)->default('image'); // image | background
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_media');
        Schema::dropIfExists('content_blocks');
    }
};
