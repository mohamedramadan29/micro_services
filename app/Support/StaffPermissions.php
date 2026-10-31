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
        // Client file «نظام الاقتراحات»: one who handles suggestions (no settings).
        'suggestions' => 'مسؤول الاقتراحات (يدير الاقتراحات ويصدّرها؛ باقي اللوحة اطلاع فقط)',
    ];

    /**
     * Suggestions box (client file «نظام الاقتراحات»): separate abilities view | manage (statuses,
     * notes, assignment, duplicates) | export (CSV with contact data) | settings (types).
     */
    public const SUGGESTION_ABILITIES = [
        'manager' => ['view', 'manage', 'export', 'settings'],
        'suggestions' => ['view', 'manage', 'export'],
        'analyst' => ['view'],
    ];

    public static function canSuggestions(?User $user, string $ability): bool
    {
        return in_array($ability, self::SUGGESTION_ABILITIES[self::role($user)] ?? [], true);
    }

    /** Active admins who may do $ability on suggestions (notified of new ones, offered as assignees). */
    public static function suggestionStaff(string $ability = 'manage')
    {
        $roles = array_keys(array_filter(self::SUGGESTION_ABILITIES, fn ($abilities) => in_array($ability, $abilities, true)));

        return User::activeAdmins()->where(function ($q) use ($roles) {
            // null / unknown admin_role = manager (see role()).
            if (in_array('manager', $roles, true)) {
                $q->whereNull('admin_role')->orWhereNotIn('admin_role', array_keys(self::ROLES));
            }
            $q->orWhereIn('admin_role', $roles);
        });
    }

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
