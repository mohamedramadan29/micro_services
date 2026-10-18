<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Answers to the admin-managed questions of the full-report form (snapshot: key, label, value). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_tests', function (Blueprint $table) {
            $table->json('answers')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_tests', fn (Blueprint $table) => $table->dropColumn('answers'));
    }
};
