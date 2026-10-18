<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Admin-chosen rules for the client classifications (first/second/repeat). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // requests = service requests only; requests_and_help = also «ساعدوني أختار» help requests.
            $table->string('client_count_mode', 30)->default('requests');
            // true = «متكرر» and «ثاني مرة» may show together; false = «متكرر» replaces «ثاني مرة».
            $table->boolean('client_tags_overlap')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['client_count_mode', 'client_tags_overlap']);
        });
    }
};
