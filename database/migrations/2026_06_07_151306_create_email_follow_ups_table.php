<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->string('name');
            $table->enum('trigger_type', ['no_open', 'no_click']);
            $table->integer('delay_days')->default(1);
            $table->string('subject');
            $table->longText('body');
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_follow_ups');
    }
};
