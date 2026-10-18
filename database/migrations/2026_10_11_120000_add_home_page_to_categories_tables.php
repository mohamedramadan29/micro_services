<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The home_page flag was added to production by hand, so guard against
     * the column already existing.
     */
    public function up(): void
    {
        foreach (['categories', 'sub_categories'] as $table) {
            if (! Schema::hasColumn($table, 'home_page')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->tinyInteger('home_page')->default(0)->after('status');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['categories', 'sub_categories'] as $table) {
            if (Schema::hasColumn($table, 'home_page')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('home_page');
                });
            }
        }
    }
};
