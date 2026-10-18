<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * website_balance is already used by the code and exists in production
     * (added by hand), so it is guarded.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'website_balance')) {
                $table->double('website_balance')->default(0);
            }
            if (! Schema::hasColumn('settings', 'repeat_client_threshold')) {
                $table->unsignedInteger('repeat_client_threshold')->default(2);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'repeat_client_threshold')) {
                $table->dropColumn('repeat_client_threshold');
            }
        });
    }
};
