<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Service 3 policy values (deadlines, limits, dispute reasons, stage templates) edited by the manager. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('contract_policy')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', fn (Blueprint $table) => $table->dropColumn('contract_policy'));
    }
};
