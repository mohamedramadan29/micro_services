<?php

use App\Models\User;
use App\Support\FreelancerPhoto;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Freelancers now add their professional photo themselves (2026-10-20). Those who already uploaded
 * a sharp account picture (≥ 400×400) get it as their professional photo, so their checklist is right.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'professional_photo')) {
            return;
        }
        User::where('account_type', User::TYPE_FREELANCER)->whereNull('professional_photo')
            ->whereNotNull('image')->where('image', '!=', '')
            ->each(fn (User $user) => FreelancerPhoto::promoteProfileImage($user));
    }

    public function down(): void
    {
        // Kept: the generated photos stay valid.
    }
};
