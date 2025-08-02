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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->references('id')->on('blog_categories')->cascadeOnDelete();
            $table->text('short_desc')->nullable();
            $table->longText('desc');
            $table->date('publish_date');
            $table->string('meta_title');
            $table->string('meta_url')->unique()->nullable();
            $table->text('meta_desc');
            $table->string('meta_keywords');
            $table->tinyInteger('status')->default(1);
            $table->string('image')->nullable();
            $table->integer('author');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
