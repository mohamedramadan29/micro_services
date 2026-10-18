<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A published form can have one draft revision: the admin edits the copy
 * while clients keep seeing the published form, then publishes the changes.
 * A revision points at its form (revision_of_id); a service now has at most
 * one main form (revision_of_id null) — enforced in code.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_forms', function (Blueprint $table) {
            $table->foreignId('revision_of_id')->nullable()->after('sub_category_id')
                ->constrained('service_forms')->cascadeOnDelete();
            // Keep an index for the sub_category foreign key before dropping the unique one.
            $table->index('sub_category_id');
        });
        Schema::table('service_forms', function (Blueprint $table) {
            $table->dropUnique(['sub_category_id']);
        });
    }

    public function down(): void
    {
        DB::table('service_forms')->whereNotNull('revision_of_id')->delete();
        Schema::table('service_forms', function (Blueprint $table) {
            $table->unique('sub_category_id');
            $table->dropIndex(['sub_category_id']);
            $table->dropConstrainedForeignId('revision_of_id');
        });
    }
};
