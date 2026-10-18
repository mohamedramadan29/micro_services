<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Service 3: «معيار القبول» per stage (how the client judges the delivery) and the one-time funding request (audit 2026-10-16). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_stages', function (Blueprint $table) {
            $table->text('acceptance')->nullable()->after('deliverable');
            $table->timestamp('funding_requested_at')->nullable(); // «موّل المرحلة التالية» sent once
        });
    }

    public function down(): void
    {
        Schema::table('contract_stages', fn (Blueprint $table) => $table->dropColumn(['acceptance', 'funding_requested_at']));
    }
};
