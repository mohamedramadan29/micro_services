<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client file «تعديلات», items 03, 11 and 12: freelancers are reviewed and
 * approved by the admin before they appear in the directory or send offers;
 * their WhatsApp number is required and checked (manually for now — no paid
 * provider); the admin sets a professional photo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp', 30)->nullable();
            $table->timestamp('whatsapp_verified_at')->nullable();
            $table->string('freelancer_status', 20)->nullable(); // pending | approved | rejected (freelancers only)
            $table->timestamp('freelancer_reviewed_at')->nullable();
            $table->foreignId('freelancer_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('freelancer_review_note', 500)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('professional_photo')->nullable();
            $table->string('bio', 500)->nullable();
            $table->string('availability', 20)->nullable();      // available | busy
            $table->string('languages')->nullable();
            $table->decimal('starting_price', 12, 2)->nullable(); // EGP

            $table->index(['account_type', 'freelancer_status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['account_type', 'freelancer_status']);
            $table->dropConstrainedForeignId('freelancer_reviewed_by');
            $table->dropColumn(['whatsapp', 'whatsapp_verified_at', 'freelancer_status', 'freelancer_reviewed_at', 'freelancer_review_note',
                'is_featured', 'professional_photo', 'bio', 'availability', 'languages', 'starting_price']);
        });
    }
};
