<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // One-hour "no offers yet" alert to the admins, sent once per request.
            $table->timestamp('no_offer_alerted_at')->nullable()->after('completed_at');
            $table->string('followup_status')->nullable()->after('no_offer_alerted_at'); // new | in_progress | resolved
            $table->text('followup_note')->nullable()->after('followup_status');
            $table->timestamp('followup_updated_at')->nullable()->after('followup_note');
            // Closing a request that was not carried out.
            $table->string('closed_reason')->nullable()->after('followup_updated_at');
            $table->text('admin_note')->nullable()->after('closed_reason');
            $table->timestamp('closed_at')->nullable()->after('admin_note');
            $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();

            $table->index(['status', 'no_offer_alerted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'no_offer_alerted_at']);
            $table->dropConstrainedForeignId('closed_by');
            $table->dropColumn(['no_offer_alerted_at', 'followup_status', 'followup_note', 'followup_updated_at', 'closed_reason', 'admin_note', 'closed_at']);
        });
    }
};
