<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The old projects/project offers system was replaced by service requests
 * (service_requests, request_offers, request_payments…). Its tables and its
 * notifications are removed. Old URLs redirect in routes/web.php.
 */
return new class extends Migration
{
    private const OLD_NOTIFICATIONS = [
        'App\\Notifications\\OfferAccepted',
        'App\\Notifications\\AcceptProjectFromAdmin',
        'App\\Notifications\\ProjectDelivery',
    ];

    private const ORIGINAL_MIGRATIONS = [
        '2024_11_07_200953_create_projects_table.php',
        '2024_11_08_075250_create_project_files_table.php',
        '2024_11_21_133720_create_project_offers_table.php',
    ];

    public function up(): void
    {
        Schema::dropIfExists('project_offers');
        Schema::dropIfExists('project_files');
        Schema::dropIfExists('projects');

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->whereIn('type', self::OLD_NOTIFICATIONS)->delete();
        }
    }

    /** Recreates the empty tables from their original migrations (data is not restored). */
    public function down(): void
    {
        foreach (self::ORIGINAL_MIGRATIONS as $file) {
            (require database_path('migrations/' . $file))->up();
        }
    }
};
