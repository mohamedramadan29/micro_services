<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Freelancers' works are reviewed before they show on the site (status 0 pending, 1 published, 2 rejected),
 * and a freelancer whose profile is ready for review is noted once (users.freelancer_ready_at).
 * Works added before the review existed were already public: they stay published.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_portfolios', function (Blueprint $table) {
            if (! Schema::hasColumn('user_portfolios', 'review_note')) {
                $table->text('review_note')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'freelancer_ready_at')) {
                $table->timestamp('freelancer_ready_at')->nullable();
            }
        });

        DB::table('user_portfolios')->where('status', 0)->update(['status' => 1]);
    }

    public function down(): void
    {
        Schema::table('user_portfolios', function (Blueprint $table) {
            $table->dropColumn(['review_note', 'reviewed_at', 'reviewed_by']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('freelancer_ready_at');
        });
    }
};
