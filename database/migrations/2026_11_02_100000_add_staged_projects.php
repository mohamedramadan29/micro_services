<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service 3, phase 1: a staged project is a request of kind «staged» (homepage card 3).
 * Freelancers offer as usual; choosing an offer opens a contract (agreement next, then
 * stage-by-stage funding) instead of paying the whole price at once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('kind', 20)->default('standard');  // standard | staged
            $table->string('project_title')->nullable();
            $table->boolean('stopped_project')->default(false); // started outside Nafizha, its freelancer stopped
            $table->text('done_before')->nullable();             // what was already done (stopped projects)
            $table->index('kind');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex(['kind']);
            $table->dropColumn(['kind', 'project_title', 'stopped_project', 'done_before']);
        });
    }
};
