<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->boolean('ab_testing_enabled')->default(false)->after('filters');
            $table->string('ab_subject_b')->nullable()->after('ab_testing_enabled');
            $table->integer('ab_split_percent')->default(50)->after('ab_subject_b');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropColumn(['ab_testing_enabled', 'ab_subject_b', 'ab_split_percent']);
        });
    }
};
