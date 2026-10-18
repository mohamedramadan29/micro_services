<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client file «تعديلات», item 05: an SEO section for many keywords, each
 * linked to a page or service, with Google Search Console figures once the
 * site is connected (never estimates presented as real data).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->string('page_url', 500)->nullable();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->string('status', 20)->default('unlinked'); // linked | review | unlinked
            $table->text('notes')->nullable();
            // Google Search Console, last 28 days (null = no data yet)
            $table->unsignedInteger('gsc_clicks')->nullable();
            $table->unsignedInteger('gsc_impressions')->nullable();
            $table->decimal('gsc_ctr', 6, 4)->nullable();
            $table->decimal('gsc_position', 6, 2)->nullable();
            $table->timestamp('gsc_synced_at')->nullable();
            $table->timestamps();

            $table->unique('keyword');
            $table->index('status');
        });

        // What people actually searched to reach the site (from Search Console).
        Schema::create('seo_search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->decimal('ctr', 6, 4)->default(0);
            $table->decimal('position', 6, 2)->nullable();
            $table->timestamp('synced_at');

            $table->unique('query');
            $table->index('impressions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_search_queries');
        Schema::dropIfExists('seo_keywords');
    }
};
