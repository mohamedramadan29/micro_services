<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->foreignId('follow_up_id')->nullable()->after('campaign_id')->constrained('email_follow_ups')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->dropForeign(['follow_up_id']);
            $table->dropColumn('follow_up_id');
        });
    }
};
