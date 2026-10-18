<?php

namespace App\Services\SiteTests;

use App\Models\admin\Setting;

/** Admin settings of the site check (settings.site_test_settings), with defaults. */
class SiteTestSettings
{
    public const CATEGORIES = [
        'performance' => ['label' => 'الأداء والسرعة', 'hint' => 'زمن التحميل ومؤشرات الأداء'],
        'seo' => ['label' => 'SEO', 'hint' => 'عناصر الصفحة والفهرسة'],
        'mobile' => ['label' => 'استخدام الهاتف', 'hint' => 'العرض والتفاعل على الهاتف'],
        'accessibility' => ['label' => 'إمكانية الوصول', 'hint' => 'وضوح عناصر الصفحة واستخدامها'],
        'best-practices' => ['label' => 'أفضل الممارسات', 'hint' => 'الأمان والممارسات الحديثة'],
    ];

    public const DEFAULTS = [
        'enabled' => true,
        'categories' => ['performance', 'seo', 'mobile', 'accessibility'],
        'devices' => ['mobile', 'desktop', 'both'],
        'max_issues' => 3,
        'show_offer_immediately' => true,
        'hourly_limit' => 5,
        // Days a check is kept (0 = forever): older anonymous checks are deleted,
        // the others keep their summary but lose the raw engine data.
        'retention_days' => 180,
        'failure_message' => 'تعذّر فحص هذا الرابط الآن. تأكد أنه رابط عام يفتح بدون كلمة مرور، ثم حاول مرة أخرى.',
        // Questions of the full-report form, managed by the admin (answers kept with their label).
        'questions' => [
            ['key' => 'goal', 'label' => 'هدف الموقع أو التحدي', 'type' => 'text', 'options' => [], 'required' => false, 'active' => true],
            ['key' => 'market', 'label' => 'السوق المستهدف', 'type' => 'select', 'options' => ['مصر', 'الخليج', 'الوطن العربي', 'عالمي'], 'required' => false, 'active' => true],
        ],
    ];

    public const QUESTION_TYPES = ['text' => 'نص قصير', 'textarea' => 'نص طويل', 'select' => 'اختيار من قائمة'];

    /** Whether a question applies to these answers (its condition, if any, is met). */
    public static function questionShown(array $question, array $answers): bool
    {
        return empty($question['show_if']) || trim((string) ($answers[$question['show_if']] ?? '')) === (string) $question['show_value'];
    }

    /** Questions the visitor sees, in order. */
    public static function questions(): array
    {
        return array_values(array_filter((array) self::get('questions'), fn ($q) => ! empty($q['active'])));
    }

    public static function all(): array
    {
        return array_replace(self::DEFAULTS, (array) (Setting::first()?->site_test_settings ?? []));
    }

    public static function get(string $key)
    {
        return self::all()[$key] ?? null;
    }

    public static function save(array $values): void
    {
        $setting = Setting::first() ?? Setting::create(['website_commission' => '10']);
        $setting->update(['site_test_settings' => array_intersect_key($values, self::DEFAULTS) + self::all()]);
    }

    /** Category options the client can tick (enabled by the admin). */
    public static function categories(): array
    {
        return array_intersect_key(self::CATEGORIES, array_flip(self::get('categories') ?: []));
    }

    public static function devices(): array
    {
        return array_intersect_key(\App\Models\SiteTest::DEVICES, array_flip(self::get('devices') ?: ['mobile']));
    }
}
