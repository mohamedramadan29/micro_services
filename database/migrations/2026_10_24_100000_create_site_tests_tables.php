<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client file «اختبر موقعك مجاناً - الخدمة الثانية»: a free check of a public
 * URL (PageSpeed Insights), its issues with evidence, the optional full report
 * reviewed by the team, and the client's yes/no to the matching service.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_tests', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->nullable()->unique();
            $table->string('token', 64);                       // guests open their result with it
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('site_tests')->nullOnDelete(); // a re-check keeps the old one
            $table->string('url', 2000);
            $table->string('host');
            $table->string('device', 10);                      // mobile | desktop | both
            $table->json('categories');
            $table->string('status', 20)->default('queued');   // queued | running | done | failed
            $table->string('failure_reason')->nullable();
            $table->string('engine', 30)->nullable();
            $table->json('scores')->nullable();                // {device: {category: 0-100|null}}
            $table->timestamp('checked_at')->nullable();
            // Full report request (contact data only from here on)
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('goal')->nullable();
            $table->timestamp('report_requested_at')->nullable();
            // Team review
            $table->string('review_status', 20)->default('none'); // none | requested | in_review | sent
            $table->text('review_summary')->nullable();
            $table->text('team_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('report_sent_at')->nullable();
            // Offer card and the client's choice
            $table->foreignId('suggested_sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->string('choice', 10)->nullable();          // yes | no
            $table->timestamp('choice_at')->nullable();
            $table->foreignId('service_request_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['review_status', 'created_at']);
        });

        Schema::create('site_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_test_id')->constrained()->cascadeOnDelete();
            $table->string('device', 10);
            $table->string('engine', 30);
            $table->boolean('ok')->default(false);
            $table->string('error')->nullable();
            $table->longText('raw')->nullable();               // the engine's raw answer, kept as evidence
            $table->timestamp('fetched_at')->nullable();
        });

        Schema::create('site_test_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_test_id')->constrained()->cascadeOnDelete();
            $table->string('source', 10)->default('tool');     // tool | team
            $table->string('audit_code', 100)->nullable();
            $table->string('category', 30)->nullable();
            $table->string('device', 10)->nullable();
            $table->string('priority', 20)->default('medium'); // high | medium | team
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('evidence', 500)->nullable();       // value the engine measured
            $table->string('page_url', 2000)->nullable();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->boolean('shown')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['site_test_id', 'source']);
        });

        Schema::create('site_test_rules', function (Blueprint $table) {
            $table->id();
            $table->string('audit_code', 100)->unique();
            $table->string('label')->nullable();               // admin's name for the result
            $table->foreignId('sub_category_id')->constrained('sub_categories')->cascadeOnDelete();
            $table->string('priority', 20)->default('medium');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->json('site_test_settings')->nullable();
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('site_test_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', fn (Blueprint $table) => $table->dropConstrainedForeignId('site_test_id'));
        Schema::table('settings', fn (Blueprint $table) => $table->dropColumn('site_test_settings'));
        Schema::dropIfExists('site_test_rules');
        Schema::dropIfExists('site_test_issues');
        Schema::dropIfExists('site_test_results');
        Schema::dropIfExists('site_tests');
    }
};
