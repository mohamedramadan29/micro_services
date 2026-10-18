<?php

namespace App\Services\Notifications;

use App\Models\admin\Setting;

/**
 * The texts of site notifications (bell + email subject/line), editable by the admin in
 * «نصوص الإشعارات» (`admin/notification-texts`). Each message has a key, a default text with
 * {placeholders} and the list of placeholders it may use; the admin's text (settings
 * `notification_templates`) replaces the default when set.
 *
 * Messages are declared in Templates/*.php (one file per area, each returning
 * [key => ['group' => …, 'label' => when it is sent, 'text' => default, 'vars' => [name => meaning]]]).
 * Code sends App\Support\Msg::t('key', ['var' => value]) instead of a hard-coded sentence.
 */
class MessageTemplates
{
    public const GROUPS = [
        'requests' => 'الطلبات والعروض (الخدمة 1)',
        'payments' => 'الدفع والمبالغ',
        'contracts' => 'مشروعك ما بيوقف: الاتفاق والمراحل',
        'clock' => 'مشروعك ما بيوقف: المواعيد والتذكيرات',
        'disputes' => 'مشروعك ما بيوقف: الخلافات وتغيير المستقل',
        'admin' => 'تنبيهات الإدارة',
        'site_tests' => 'اختبر موقعك',
        'other' => 'أخرى',
    ];

    private static ?array $definitions = null;

    /** Every declared message, key => definition. */
    public static function all(): array
    {
        if (self::$definitions === null) {
            $all = [];
            foreach (glob(__DIR__ . '/Templates/*.php') ?: [] as $file) {
                $all += (array) require $file;
            }
            self::$definitions = $all;
        }

        return self::$definitions;
    }

    public static function overrides(): array
    {
        return array_filter((array) (Setting::first()?->notification_templates ?? []), fn ($text) => is_string($text) && trim($text) !== '');
    }

    /** The text to send: the admin's version if any, else the default, with the placeholders filled. */
    public static function render(string $key, array $vars = []): string
    {
        $definition = self::all()[$key] ?? null;
        $text = self::overrides()[$key] ?? ($definition['text'] ?? $key);
        // A placeholder with no value (or a typo) never reaches the reader as «{…}». This cleans the
        // template only, before the values go in, so what users wrote (a reason, a note) stays as is.
        $text = preg_replace_callback('/\{([a-z_]+)\}/', fn ($m) => array_key_exists($m[1], $vars) ? $m[0] : '', $text);
        $text = trim(preg_replace('/ {2,}/', ' ', $text));

        return strtr($text, collect($vars)->mapWithKeys(fn ($value, $name) => ['{' . $name . '}' => (string) $value])->all());
    }

    /** Placeholders used in a text that the message does not offer. */
    public static function unknownPlaceholders(string $key, string $text): array
    {
        preg_match_all('/\{([a-z_]+)\}/', $text, $m);

        return array_values(array_diff(array_unique($m[1]), array_keys(self::all()[$key]['vars'] ?? [])));
    }

    public static function save(array $texts): void
    {
        $setting = Setting::first() ?? Setting::create(['website_commission' => '10']);
        $current = (array) ($setting->notification_templates ?? []);
        foreach ($texts as $key => $text) {
            if (! array_key_exists($key, self::all())) {
                continue;
            }
            $text = trim((string) $text);
            if ($text === '' || $text === self::all()[$key]['text']) {
                unset($current[$key]); // back to the default
            } else {
                $current[$key] = $text;
            }
        }
        $setting->update(['notification_templates' => $current]);
    }
}
