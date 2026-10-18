<?php

namespace App\Support;

use App\Models\admin\Setting;
use App\Models\User;

/**
 * Staff roles for service 3 decisions (client decision 2026-10-16):
 * - manager: everything (an admin without a role is a manager, as before roles existed);
 * - disputes (مراجع خلافات): decides disputes, but money decisions become a proposal;
 * - accounts (محاسب): carries out money decisions and approves proposals, up to the threshold;
 * money decisions above `settings.contract_approval_threshold` (0 = no limit) need the manager.
 */
class StaffPermissions
{
    public const ROLES = [
        'manager' => 'مدير (كل الصلاحيات)',
        'disputes' => 'مراجع خلافات (يقرر دون تحويل أموال)',
        'accounts' => 'محاسب (الحسابات والتحويلات والاسترجاع)',
        // Client file «المربعات الخمسة»: site content roles.
        'content' => 'محرر محتوى (يعدّل نصوص الواجهة كمسودة، والنشر للمدير)',
        'analyst' => 'دعم / تحليلات (اطلاع على الطلبات والتقارير دون تعديل)',
    ];

    /**
     * Managed site content: 'publish' (manager: everything, incl. destinations, publishing and rollback),
     * 'edit' (content editor: texts as drafts, not the sensitive fields), else 'view'.
     */
    public static function contentAccess(?User $user): string
    {
        return match (self::role($user)) {
            'manager' => 'publish',
            'content' => 'edit',
            default => 'view',
        };
    }

    /** Decisions that move money. */
    public const MONEY_DECISIONS = ['release', 'refund', 'split', 'change_freelancer'];

    public static function role(?User $user): ?string
    {
        if (! $user || $user->account_type !== User::TYPE_ADMIN || (int) $user->status !== 1) {
            return null;
        }

        return array_key_exists((string) $user->admin_role, self::ROLES) ? $user->admin_role : 'manager';
    }

    public static function isManager(?User $user): bool
    {
        return self::role($user) === 'manager';
    }

    public static function threshold(): float
    {
        return (float) (Setting::first()?->contract_approval_threshold ?? 0);
    }

    /** 'direct' (do it), 'propose' (save for approval) or 'deny'. */
    public static function mode(?User $user, bool $money, float $amount): string
    {
        return match (self::role($user)) {
            'manager' => 'direct',
            'disputes' => $money ? 'propose' : 'direct',
            'accounts' => ! $money ? 'deny' : (self::aboveThreshold($amount) ? 'propose' : 'direct'),
            default => 'deny',
        };
    }

    /** Who may approve a proposed money decision of this amount. */
    public static function canApprove(?User $user, float $amount): bool
    {
        return match (self::role($user)) {
            'manager' => true,
            'accounts' => ! self::aboveThreshold($amount),
            default => false,
        };
    }

    public static function aboveThreshold(float $amount): bool
    {
        $threshold = self::threshold();

        return $threshold > 0 && $amount > $threshold;
    }
}
