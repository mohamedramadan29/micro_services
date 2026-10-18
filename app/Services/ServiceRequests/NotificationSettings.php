<?php

namespace App\Services\ServiceRequests;

use App\Models\admin\Setting;

/**
 * Which request-system notifications also go out by email (site setting).
 * The in-site bell entry is always created.
 */
class NotificationSettings
{
    /** key => [label, who receives it, emailed by default] */
    public const EMAILS = [
        'new_offer' => ['عرض جديد أو معدّل على الطلب', 'العميل', true],
        'offer_messages' => ['رسالة جديدة في محادثة العرض', 'العميل والمستقل', false],
        'offer_selected' => ['اختيار العرض ودفع قيمته', 'المستقل', true],
        'offer_not_selected' => ['اختيار عرض آخر', 'المستقلون الآخرون', false],
        'work_delivered' => ['تسليم العمل', 'العميل', true],
        'payment_released' => ['تحويل المستحقات للرصيد', 'المستقل', true],
        'team_messages' => ['رسالة من فريق نفّذها', 'العميل', true],
        'request_updates' => ['إغلاق الطلب أو نشره أو اعتماده', 'العميل والمستقل', true],
        'contracts' => ['الاتفاق والمراحل في «مشروعك ما بيوقف»', 'العميل والمستقل', true],
        'request_changes' => ['تعديل الطلب، وطلبات التعديل بعد البدء وردودها', 'العميل والمستقل', true],
        'admin_alerts' => ['تنبيهات الأدمن (لا عروض بعد ساعة، استفسار جديد، دفعة مكررة، رسائل العملاء)', 'الأدمن', true],
        'help_requests' => ['طلب «ساعدوني أختار» جديد أو مُسند لموظف', 'الأدمن', true],
    ];

    public static function emailEnabled(string $key): bool
    {
        if (! isset(self::EMAILS[$key])) {
            return false;
        }
        $saved = (array) (Setting::first()?->request_notification_emails ?? []);

        return (bool) ($saved[$key] ?? self::EMAILS[$key][2]);
    }

    /** Current value of every key (saved or default), for the settings form. */
    public static function all(): array
    {
        $saved = (array) (Setting::first()?->request_notification_emails ?? []);

        return collect(self::EMAILS)->map(fn ($meta, $key) => (bool) ($saved[$key] ?? $meta[2]))->all();
    }
}
