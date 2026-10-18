<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Per-type email switches for request-system notifications (null = defaults). */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('request_notification_emails')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('request_notification_emails');
        });
    }
};
