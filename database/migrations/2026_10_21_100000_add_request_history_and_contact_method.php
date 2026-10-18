<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec «رسم مسار العميل والأدمن والمستقلين», steps 4, 6, 14, 19 and page 7:
 * a log of every status change and admin action on a request, the client's
 * preferred contact method, and admin invitations to matching freelancers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who did it; null = the system
            $table->string('type', 30);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->foreignId('freelancer_id')->nullable()->constrained('users')->nullOnDelete(); // invitations
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['service_request_id', 'id']);
            $table->index(['type', 'freelancer_id']);
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('contact_method', 20)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', fn (Blueprint $table) => $table->dropColumn('contact_method'));
        Schema::dropIfExists('service_request_events');
    }
};
