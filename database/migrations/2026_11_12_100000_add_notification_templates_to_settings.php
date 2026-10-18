<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Admin-edited notification texts (key => text); see App\Services\Notifications\MessageTemplates. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('notification_templates')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', fn (Blueprint $table) => $table->dropColumn('notification_templates'));
    }
};
