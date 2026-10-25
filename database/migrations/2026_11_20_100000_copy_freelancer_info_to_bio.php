<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Freelancers have one bio (2026-10-20): a freelancer who wrote «النبذة التعريفية» (users.info)
 * but never the profile bio gets it copied, so the checklist, the directory and search see it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'bio') || ! Schema::hasColumn('users', 'info')) {
            return;
        }
        DB::table('users')
            ->where('account_type', 'بائع')
            ->where(fn ($q) => $q->whereNull('bio')->orWhere('bio', ''))
            ->whereNotNull('info')->where('info', '!=', '')
            ->update(['bio' => DB::raw('info')]);
    }

    public function down(): void
    {
        // Nothing to undo: the copied text stays valid.
    }
};
