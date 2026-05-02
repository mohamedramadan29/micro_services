<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['facebook', 'instagram', 'tiktok', 'youtube', 'twitter', 'linkedin']);
            $table->string('account_name');
            $table->string('account_id')->nullable();   // Platform User/Page ID
            $table->text('access_token');               // مشفّر
            $table->text('refresh_token')->nullable();  // مشفّر
            $table->datetime('token_expires_at')->nullable();
            $table->string('page_id')->nullable();      // لـ Facebook Pages
            $table->string('page_name')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();           // بيانات إضافية
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
