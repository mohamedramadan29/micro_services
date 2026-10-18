<?php

namespace App\Services\Contracts;

use App\Models\admin\Setting;
use App\Models\ContractDispute;

/**
 * Service 3 policy values (client decisions 2026-10-16 are the defaults), edited by the
 * manager in admin «سياسة المشاريع» (`settings.contract_policy`). Read them here, never
 * from hard-coded numbers.
 */
class ContractPolicy
{
    public const NUMBERS = [
        'due_reminder_days' => ['label' => 'تذكير المستقل قبل موعد التسليم (أيام)', 'default' => 1, 'min' => 1, 'max' => 7],
        'review_days' => ['label' => 'مهلة مراجعة العميل للتسليم (أيام)', 'default' => 5, 'min' => 1, 'max' => 30],
        'review_reminder_days' => ['label' => 'تذكير العميل بالمراجعة بعد (أيام)', 'default' => 3, 'min' => 1, 'max' => 30],
        'late_change_days' => ['label' => 'أيام التأخير التي يحق بعدها للعميل طلب تغيير المستقل', 'default' => 7, 'min' => 1, 'max' => 60],
        'pause_reminder_days' => ['label' => 'تذكير العميل بطلب المستقل (إيقاف العدّاد) بعد (أيام)', 'default' => 3, 'min' => 1, 'max' => 30],
        'pause_max_days' => ['label' => 'إحالة الإيقاف للإدارة بعد (أيام)', 'default' => 7, 'min' => 1, 'max' => 60],
        'dispute_respond_days' => ['label' => 'مهلة رد الطرف الآخر في الخلاف (أيام)', 'default' => 3, 'min' => 1, 'max' => 30],
        'rating_reminder_days' => ['label' => 'تذكير التقييم بعد اكتمال المشروع (أيام)', 'default' => 2, 'min' => 1, 'max' => 30],
        'default_revisions' => ['label' => 'عدد التعديلات الافتراضي لكل مرحلة', 'default' => 2, 'min' => 0, 'max' => 20],
        'max_stages' => ['label' => 'أقصى عدد مراحل في الاتفاق', 'default' => 10, 'min' => 1, 'max' => 20],
        'max_files' => ['label' => 'أقصى عدد ملفات في التسليم أو الخلاف', 'default' => 10, 'min' => 1, 'max' => 20],
        'file_max_mb' => ['label' => 'أقصى حجم للملف (ميجابايت)', 'default' => 20, 'min' => 1, 'max' => 100],
    ];

    /** Stage templates offered in the agreement form; the first one is the starting proposal. Shares are % of the offer. */
    public const DEFAULT_TEMPLATES = [
        ['name' => 'ثلاث مراحل (تخطيط، تنفيذ، تسليم)', 'stages' => [
            ['title' => 'التخطيط والبداية', 'deliverable' => 'خطة العمل وتفاصيل التنفيذ المتفق عليها', 'share' => 30],
            ['title' => 'التنفيذ', 'deliverable' => 'تنفيذ الجزء الأساسي من العمل', 'share' => 40],
            ['title' => 'التسليم النهائي', 'deliverable' => 'تسليم العمل كاملًا بملفاته النهائية', 'share' => 30],
        ]],
        ['name' => 'مرحلتان (نصف ونصف)', 'stages' => [
            ['title' => 'النصف الأول', 'deliverable' => 'تسليم أول جزء من العمل للمراجعة', 'share' => 50],
            ['title' => 'التسليم النهائي', 'deliverable' => 'تسليم العمل كاملًا بملفاته النهائية', 'share' => 50],
        ]],
        ['name' => 'مرحلة واحدة', 'stages' => [
            ['title' => 'تنفيذ المشروع', 'deliverable' => 'تسليم العمل كاملًا بملفاته النهائية', 'share' => 100],
        ]],
    ];

    public static function all(): array
    {
        return (array) (Setting::first()?->contract_policy ?? []);
    }

    public static function number(string $key): int
    {
        $definition = self::NUMBERS[$key];
        $value = self::all()[$key] ?? $definition['default'];

        return max($definition['min'], min($definition['max'], (int) $value));
    }

    /** key => label; keys of older disputes stay readable through label(). */
    public static function disputeReasons(): array
    {
        $saved = self::all()['dispute_reasons'] ?? null;

        return is_array($saved) && $saved ? $saved : ContractDispute::REASONS;
    }

    public static function disputeReasonLabel(?string $key): string
    {
        return self::disputeReasons()[$key] ?? ContractDispute::REASONS[$key] ?? (string) $key;
    }

    public static function templates(): array
    {
        $saved = self::all()['stage_templates'] ?? null;

        return is_array($saved) && $saved ? array_values($saved) : self::DEFAULT_TEMPLATES;
    }

    public static function save(array $values): void
    {
        $setting = Setting::first() ?? Setting::create(['website_commission' => '10']);
        $before = self::all();
        $after = array_replace($before, $values);
        // Who changed what and when (spec page 18 #14).
        $changes = [];
        foreach ($after as $key => $value) {
            $old = $before[$key] ?? (self::NUMBERS[$key]['default'] ?? null);
            if ($old != $value) {
                $changes[$key] = [$old, $value];
            }
        }
        $setting->update(['contract_policy' => $after]);
        if ($changes) {
            \App\Models\ContractPolicyChange::create(['user_id' => \Illuminate\Support\Facades\Auth::id(), 'changes' => $changes]);
        }
    }
}
