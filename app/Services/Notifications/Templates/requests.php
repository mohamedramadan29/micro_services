<?php

/*
 * Notification texts of the service request system (requests, offers, payments of a request,
 * admin alerts, freelancer approval, free site check). See App\Services\Notifications\MessageTemplates.
 */
return [
    // ── Requests and offers ─────────────────────────────────────────────
    'requests.offer_new' => [
        'group' => 'requests',
        'label' => 'للعميل: وصل عرض جديد على طلبه',
        'text' => 'وصل عرض جديد على طلبك {number} ({service})',
        'vars' => ['number' => 'رقم الطلب', 'service' => 'اسم الخدمة'],
    ],
    'requests.offer_updated' => [
        'group' => 'requests',
        'label' => 'للعميل: عدّل مستقل عرضه على طلبه',
        'text' => 'تم تعديل عرض على طلبك {number} ({service})',
        'vars' => ['number' => 'رقم الطلب', 'service' => 'اسم الخدمة'],
    ],
    'requests.offer_message_from_client' => [
        'group' => 'requests',
        'label' => 'للمستقل: رد العميل في محادثة العرض',
        'text' => 'رد العميل على عرضك في الطلب {number}',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.offer_message_from_freelancer' => [
        'group' => 'requests',
        'label' => 'للعميل: رسالة جديدة من المستقل في محادثة العرض',
        'text' => 'رسالة جديدة من المستقل على طلبك {number}',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.offer_not_selected' => [
        'group' => 'requests',
        'label' => 'لباقي المستقلين: اختار العميل عرضًا آخر',
        'text' => 'اختار العميل عرضًا آخر للطلب {number}',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.offer_selected' => [
        'group' => 'requests',
        'label' => 'للمستقل: اختار العميل عرضه ودفع قيمته',
        'text' => 'تم اختيار عرضك على الطلب {number} ودفع العميل قيمته. ابدأ التنفيذ',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.work_delivered' => [
        'group' => 'requests',
        'label' => 'للعميل: سلّم المستقل العمل وينتظر تأكيد الاستلام',
        'text' => 'سلّم المستقل العمل على طلبك {number}، راجعه وأكّد الاستلام',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.closed' => [
        'group' => 'requests',
        'label' => 'للعميل: أُغلق طلبه دون تنفيذ (لم يُدفع شيء)',
        'text' => 'تم إغلاق طلبك {number} ({reason})',
        'vars' => ['number' => 'رقم الطلب', 'reason' => 'سبب الإغلاق'],
    ],
    'requests.closed_refunded' => [
        'group' => 'requests',
        'label' => 'للعميل: أُغلق طلبه دون تنفيذ واسترجع المبلغ المدفوع',
        'text' => 'تم إغلاق طلبك {number} ({reason}) واسترجاع المبلغ المدفوع',
        'vars' => ['number' => 'رقم الطلب', 'reason' => 'سبب الإغلاق'],
    ],
    'requests.closed_for_freelancer' => [
        'group' => 'requests',
        'label' => 'للمستقل المختار: أُغلق الطلب قبل التنفيذ',
        'text' => 'أُغلق الطلب {number} قبل التنفيذ ({reason})',
        'vars' => ['number' => 'رقم الطلب', 'reason' => 'سبب الإغلاق'],
    ],
    'requests.inquiry_published' => [
        'group' => 'requests',
        'label' => 'للعميل: نُشر استفسار الأسعار للمستقلين بموافقته',
        'text' => 'تم نشر طلبك {number} للمستقلين المختصين بناءً على موافقتك',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.completed_by_team' => [
        'group' => 'requests',
        'label' => 'للعميل: اعتمد فريق نفّذها اكتمال طلبه نيابة عنه',
        'text' => 'اعتمد فريق نفّذها اكتمال طلبك {number}',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.freelancer_invited' => [
        'group' => 'requests',
        'label' => 'للمستقل: دعاه فريق نفّذها لإرسال عرض على طلب',
        'text' => 'فريق نفّذها يدعوك لإرسال عرض على الطلب {number} ({service})',
        'vars' => ['number' => 'رقم الطلب', 'service' => 'اسم الخدمة'],
    ],
    'requests.team_message' => [
        'group' => 'requests',
        'label' => 'للعميل: رسالة من فريق نفّذها على طلبه',
        'text' => 'رسالة من فريق نفّذها بخصوص طلبك {number}',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.edited_for_bidders' => [
        'group' => 'requests',
        'label' => 'للمستقلين أصحاب العروض: عدّل العميل الطلب بعد عروضهم',
        'text' => 'عدّل العميل الطلب {number} بعد عرضك. راجع التفاصيل وحدّث عرضك إن لزم.',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.project_edited_for_bidders' => [
        'group' => 'requests',
        'label' => 'للمستقلين أصحاب العروض: عدّل العميل المشروع المنشور مباشرة بعد عروضهم',
        'text' => 'عدّل العميل المشروع {number} بعد عرضك. راجع التفاصيل وحدّث عرضك إن لزم.',
        'vars' => ['number' => 'رقم المشروع'],
    ],
    'requests.freelancer_approved' => [
        'group' => 'requests',
        'label' => 'للمستقل: اعتمد الأدمن حسابه كمستقل',
        'text' => 'تم اعتماد حسابك كمستقل في نفّذها، وأصبحت تظهر في صفحة المستقلين ويمكنك إرسال العروض.',
        'vars' => [],
    ],
    'requests.freelancer_verified' => [
        'group' => 'requests',
        'label' => 'للمستقل: وثّق الأدمن حسابه يدويًا (قبل الاعتماد)',
        'text' => 'وثّق فريق نفّذها حسابك. يبقى اعتماد ملفك من الفريق حتى تظهر في صفحة المستقلين وترسل العروض.',
        'vars' => [],
    ],
    'requests.service_published' => [
        'group' => 'requests',
        'label' => 'للمستقل: نشر الفريق خدمته',
        'text' => 'تم نشر خدمتك «{title}» وأصبحت متاحة للشراء.',
        'vars' => ['title' => 'اسم الخدمة'],
    ],
    'requests.service_rejected' => [
        'group' => 'requests',
        'label' => 'للمستقل: لم يقبل الفريق خدمته',
        'text' => 'لم تُقبل خدمتك «{title}»: {reason}',
        'vars' => ['title' => 'اسم الخدمة', 'reason' => 'السبب'],
    ],
    'requests.portfolio_published' => [
        'group' => 'requests',
        'label' => 'للمستقل: نشر الفريق عملًا من معرض أعماله',
        'text' => 'تم نشر عملك «{title}» ويظهر الآن في ملفك.',
        'vars' => ['title' => 'عنوان العمل'],
    ],
    'requests.portfolio_rejected' => [
        'group' => 'requests',
        'label' => 'للمستقل: لم يقبل الفريق عملًا من معرض أعماله',
        'text' => 'لم يُقبل عملك «{title}»: {reason}',
        'vars' => ['title' => 'عنوان العمل', 'reason' => 'السبب'],
    ],
    'requests.freelancer_rejected' => [
        'group' => 'requests',
        'label' => 'للمستقل: لم يعتمد الأدمن حسابه كمستقل',
        'text' => 'لم يُعتمد حسابك كمستقل بعد: {reason}',
        'vars' => ['reason' => 'سبب عدم الاعتماد'],
    ],

    // ── Changes after the work started ──────────────────────────────────
    'requests.change_proposed' => [
        'group' => 'requests',
        'label' => 'للمستقل المختار: أرسل العميل طلب تعديل بعد بدء العمل',
        'text' => 'طلب تعديل على الطلب {number}{changes} — بانتظار موافقتك',
        'vars' => ['number' => 'رقم الطلب', 'changes' => 'ملخص التغيير بين قوسين (السعر/الموعد)، فارغ إن لم يتغيرا'],
    ],
    'requests.change_rejected' => [
        'group' => 'requests',
        'label' => 'للعميل: رفض المستقل طلب التعديل (بدون ملاحظة)',
        'text' => 'رفض المستقل طلب التعديل على الطلب {number}',
        'vars' => ['number' => 'رقم الطلب'],
    ],
    'requests.change_rejected_note' => [
        'group' => 'requests',
        'label' => 'للعميل: رفض المستقل طلب التعديل مع ملاحظة',
        'text' => 'رفض المستقل طلب التعديل على الطلب {number}: {note}',
        'vars' => ['number' => 'رقم الطلب', 'note' => 'ملاحظة المستقل'],
    ],
    'requests.change_awaiting_payment' => [
        'group' => 'payments',
        'label' => 'للعميل: وافق المستقل على التعديل وبقي دفع فرق السعر',
        'text' => 'وافق المستقل على تعديل الطلب {number}. ادفع الفرق {amount} ليُعتمد التعديل.',
        'vars' => ['number' => 'رقم الطلب', 'amount' => 'الفرق المطلوب دفعه'],
    ],
    'requests.change_applied' => [
        'group' => 'requests',
        'label' => 'للعميل: اعتُمد تعديل طلبه',
        'text' => 'اعتُمد تعديل الطلب {number}{changes}',
        'vars' => ['number' => 'رقم الطلب', 'changes' => 'ملخص التغيير بين قوسين (السعر/الموعد)، فارغ إن لم يتغيرا'],
    ],
    'requests.change_applied_refund' => [
        'group' => 'payments',
        'label' => 'للعميل: اعتُمد تعديل طلبه بسعر أقل وأُعيد الفرق إلى رصيده',
        'text' => 'اعتُمد تعديل الطلب {number}{changes}، وأُعيد {amount} إلى رصيدك في الموقع',
        'vars' => ['number' => 'رقم الطلب', 'changes' => 'ملخص التغيير بين قوسين (السعر/الموعد)', 'amount' => 'المبلغ المعاد إلى الرصيد'],
    ],
    'requests.change_applied_freelancer' => [
        'group' => 'requests',
        'label' => 'للمستقل المختار: اعتُمد تعديل الطلب',
        'text' => 'اعتُمد تعديل الطلب {number}{changes}',
        'vars' => ['number' => 'رقم الطلب', 'changes' => 'ملخص التغيير بين قوسين (السعر/الموعد)، فارغ إن لم يتغيرا'],
    ],

    // ── Payments ────────────────────────────────────────────────────────
    'payments.request_released' => [
        'group' => 'payments',
        'label' => 'للمستقل: أكّد العميل الاستلام وأُضيف المبلغ إلى رصيده',
        'text' => 'أكّد العميل استلام الطلب {number}، وأُضيف {amount} إلى رصيدك',
        'vars' => ['number' => 'رقم الطلب', 'amount' => 'المبلغ المضاف إلى الرصيد'],
    ],

    // ── Admin alerts ────────────────────────────────────────────────────
    'admin.request_inquiry_new' => [
        'group' => 'admin',
        'label' => 'للإدارة: حفظ عميل استفسار أسعار جديد',
        'text' => 'استفسار أسعار جديد {number} ({service}) من {name}',
        'vars' => ['number' => 'رقم الطلب', 'service' => 'اسم الخدمة', 'name' => 'اسم العميل'],
    ],
    'admin.request_no_offers' => [
        'group' => 'admin',
        'label' => 'للإدارة: طلب منشور بلا عروض بعد ساعة',
        'text' => 'تنبيه: لا توجد عروض بعد ساعة على الطلب {number} ({service})',
        'vars' => ['number' => 'رقم الطلب', 'service' => 'اسم الخدمة'],
    ],
    'admin.request_payment_duplicate' => [
        'group' => 'admin',
        'label' => 'للإدارة: دفعة مكررة على طلب مأخوذ تحتاج استرجاعًا',
        'text' => 'دفعة مكررة تحتاج استرجاعًا على الطلب {number} ({amount})',
        'vars' => ['number' => 'رقم الطلب', 'amount' => 'المبلغ'],
    ],
    'admin.request_payment_change_cancelled' => [
        'group' => 'admin',
        'label' => 'للإدارة: دُفع فرق تعديل أُلغي قبل الدفع ويحتاج استرجاعًا',
        'text' => 'فرق تعديل أُلغي قبل الدفع، يحتاج استرجاعًا على الطلب {number} ({amount})',
        'vars' => ['number' => 'رقم الطلب', 'amount' => 'المبلغ'],
    ],
    'admin.request_client_message' => [
        'group' => 'admin',
        'label' => 'للإدارة: رسالة من العميل لفريق نفّذها على طلبه',
        'text' => 'رسالة من العميل {name} على الطلب {number}',
        'vars' => ['name' => 'اسم العميل', 'number' => 'رقم الطلب'],
    ],
    'admin.help_request_new' => [
        'group' => 'admin',
        'label' => 'للإدارة: طلب «ساعدوني أختار» جديد',
        'text' => 'طلب مساعدة جديد {number} ({service}) من {name}',
        'vars' => ['number' => 'رقم طلب المساعدة', 'service' => 'الخدمة', 'name' => 'اسم العميل'],
    ],
    'admin.help_request_assigned' => [
        'group' => 'admin',
        'label' => 'للأدمن المسؤول: أُسند إليه طلب «ساعدوني أختار»',
        'text' => 'أُسند إليك طلب المساعدة {number} ({service}) من {name}',
        'vars' => ['number' => 'رقم طلب المساعدة', 'service' => 'الخدمة', 'name' => 'اسم العميل'],
    ],
    'admin.freelancer_pending_review' => [
        'group' => 'admin',
        'label' => 'للإدارة: مستقل أرسل ملفه وينتظر المراجعة',
        'text' => 'مستقل ينتظر المراجعة: {name}',
        'vars' => ['name' => 'اسم المستقل'],
    ],
    'admin.freelancer_ready' => [
        'group' => 'admin',
        'label' => 'للإدارة: مستقل أكمل بياناته وجاهز للمراجعة (مرة واحدة)',
        'text' => 'مستقل جاهز للمراجعة والاعتماد: {name}',
        'vars' => ['name' => 'اسم المستقل'],
    ],
    'admin.service_pending' => [
        'group' => 'admin',
        'label' => 'للإدارة: مستقل أضاف خدمة أو عدّلها وتنتظر المراجعة قبل الظهور',
        'text' => 'خدمة بانتظار المراجعة: «{title}» من {name}',
        'vars' => ['title' => 'اسم الخدمة', 'name' => 'اسم المستقل'],
    ],
    'admin.portfolio_pending' => [
        'group' => 'admin',
        'label' => 'للإدارة: مستقل أضاف عملًا أو عدّله وينتظر المراجعة قبل الظهور',
        'text' => 'عمل جديد بانتظار المراجعة: «{title}» من {name}',
        'vars' => ['title' => 'عنوان العمل', 'name' => 'اسم المستقل'],
    ],

    // ── Free site check ─────────────────────────────────────────────────
    'site_tests.offer_yes' => [
        'group' => 'site_tests',
        'label' => 'للإدارة: وافق العميل على تنفيذ الخدمة المقترحة بعد الفحص',
        'text' => '{name} وافق على تنفيذ خدمة {service} (فحص {number})',
        'vars' => ['name' => 'اسم العميل', 'service' => 'الخدمة المقترحة', 'number' => 'رقم الفحص'],
    ],
    'site_tests.offer_no' => [
        'group' => 'site_tests',
        'label' => 'للإدارة: رفض العميل تنفيذ الخدمة المقترحة الآن',
        'text' => '{name} لا يريد تنفيذ خدمة {service} الآن (فحص {number})',
        'vars' => ['name' => 'اسم العميل', 'service' => 'الخدمة المقترحة', 'number' => 'رقم الفحص'],
    ],
    'site_tests.no_rule' => [
        'group' => 'site_tests',
        'label' => 'للإدارة: انتهى فحص بمشكلات لا ترتبط بأي خدمة',
        'text' => 'فحص {number} ({host}) انتهى بمشكلات لا ترتبط بأي خدمة — يحتاج مراجعتك لاختيار الخدمة',
        'vars' => ['number' => 'رقم الفحص', 'host' => 'عنوان الموقع'],
    ],
    'site_tests.published' => [
        'group' => 'site_tests',
        'label' => 'للإدارة: نُشر طلب للمستقلين من فحص موقع',
        'text' => 'نُشر طلب {request} للمستقلين من فحص {number} ({service})',
        'vars' => ['request' => 'رقم الطلب', 'number' => 'رقم الفحص', 'service' => 'الخدمة المقترحة'],
    ],
    'site_tests.report_requested' => [
        'group' => 'site_tests',
        'label' => 'للإدارة: طلب العميل التقرير الكامل للفحص',
        'text' => '{name} طلب التقرير الكامل لفحص {host} ({number})',
        'vars' => ['name' => 'اسم العميل', 'host' => 'عنوان الموقع', 'number' => 'رقم الفحص'],
    ],
];
