<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client decision 2026-10-16: a freelancer is verified by a confirmed email or by the admin,
 * not by WhatsApp. The admin's manual check keeps its date under the new name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->renameColumn('whatsapp_verified_at', 'admin_verified_at'));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->renameColumn('admin_verified_at', 'whatsapp_verified_at'));
    }
};
