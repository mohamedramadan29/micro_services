<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client file «تعديلات», item 08: where visitors come from and how far they
 * get (service → form → cart → payment → completed). First-party only, an
 * anonymous visitor id, recorded only after the visitor accepts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 40)->index();             // anonymous cookie id
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 30)->index();                 // google, facebook, blog, direct, …
            $table->string('medium', 60)->nullable();              // utm_medium, cpc, organic, social
            $table->string('campaign', 120)->nullable();           // utm_campaign
            $table->string('referrer_host')->nullable();
            $table->string('landing_path', 500);
            $table->string('last_step', 30)->nullable();           // furthest funnel step reached
            $table->unsignedSmallInteger('events_count')->default(0);
            $table->timestamp('started_at')->useCurrent()->index();
            $table->timestamp('last_seen_at')->useCurrent();
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('analytics_visits')->cascadeOnDelete();
            $table->string('type', 30);                            // page_view, service_view, form_start, cart_add, payment_start, order_complete, …
            $table->string('path', 500)->nullable();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['visit_id', 'id']);
            $table->index(['type', 'created_at']);
        });

        Schema::table('online_payments', function (Blueprint $table) {
            $table->foreignId('visit_id')->nullable()->constrained('analytics_visits')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('online_payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('visit_id'));
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_visits');
    }
};
