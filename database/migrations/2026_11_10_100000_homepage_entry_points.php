<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client file «المربعات الخمسة»: where a request came from (guided questions, direct
 * «انشر مشروعك», the site check), the context a «stuck» help request was sent from,
 * when a freelancer completed the join checklist, and the publish history of the
 * managed content (change log + rollback).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('source', 20)->default('guided')->index(); // guided | direct | site_test
        });
        Schema::table('help_requests', function (Blueprint $table) {
            $table->text('context')->nullable(); // e.g. the service and answers of the request form the client got stuck in
            $table->string('source', 30)->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('freelancer_profile_completed_at')->nullable();
        });
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('page', 40);          // ContentAreas::PAGES key
            $table->json('snapshot');            // {area: [published fields…]} right after publishing
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 20)->default('publish'); // publish | rollback
            $table->timestamp('created_at')->useCurrent();
            $table->index(['page', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('freelancer_profile_completed_at'));
        Schema::table('help_requests', fn (Blueprint $table) => $table->dropColumn(['context', 'source']));
        Schema::table('service_requests', fn (Blueprint $table) => $table->dropColumn('source'));
    }
};
