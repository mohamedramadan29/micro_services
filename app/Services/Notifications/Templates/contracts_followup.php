<?php

// Service 3 «مشروعك ما بيوقف»: clock (pauses, due dates, lateness, review and rating reminders),
// disputes and freelancer change. Used by StageClock, DisputeService and FreelancerChangeService.
return [
    // StageClock — pauses
    'clock.pause_requested' => [
        'group' => 'clock',
        'label' => 'للعميل: عند إيقاف المستقل العدّاد وطلبه شيئًا منه',
        'text' => 'المستقل يحتاج منك لمشروع {number}: {request} — العدّاد متوقف حتى تردّ (حتى {days} أيام).',
        'vars' => ['number' => 'رقم العقد', 'request' => 'ما يطلبه المستقل', 'days' => 'أقصى مدة لإيقاف العدّاد بالأيام'],
    ],
    'clock.pause_answered' => [
        'group' => 'clock',
        'label' => 'للمستقل: عند رد العميل على طلبه واستئناف العدّاد',
        'text' => 'أرسل العميل المطلوب لمشروع {number}، واستؤنف العدّاد. موعد التسليم الجديد {date}.',
        'vars' => ['number' => 'رقم العقد', 'date' => 'موعد التسليم الجديد'],
    ],
    'clock.pause_objected' => [
        'group' => 'clock',
        'label' => 'للمستقل: عند اعتراض العميل على إيقاف العدّاد',
        'text' => 'اعترض العميل على طلبك لمشروع {number}: {reason}. استؤنف العدّاد دون تمديد، وستراجع الإدارة الأمر.',
        'vars' => ['number' => 'رقم العقد', 'reason' => 'سبب الاعتراض'],
    ],
    'clock.admin_pause_objected' => [
        'group' => 'admin',
        'label' => 'للإدارة: عند اعتراض العميل على إيقاف العدّاد',
        'text' => 'اعتراض على إيقاف العدّاد في {number}: {reason}',
        'vars' => ['number' => 'رقم العقد', 'reason' => 'سبب الاعتراض'],
    ],
    'clock.pause_reminder' => [
        'group' => 'clock',
        'label' => 'للعميل: تذكير بعد أيام من إيقاف العدّاد دون رده',
        'text' => 'تذكير: المستقل ما زال ينتظر منك لمشروع {number}: {request}',
        'vars' => ['number' => 'رقم العقد', 'request' => 'ما يطلبه المستقل'],
    ],
    'clock.admin_pause_escalated' => [
        'group' => 'admin',
        'label' => 'للإدارة: بعد أقصى مدة لإيقاف العدّاد دون رد العميل',
        'text' => 'العدّاد متوقف منذ {days} أيام في {number} دون رد العميل: مدّ المهلة أو احسم المرحلة',
        'vars' => ['number' => 'رقم العقد', 'days' => 'أقصى مدة لإيقاف العدّاد بالأيام'],
    ],

    // StageClock — ratings
    'clock.rating_reminder' => [
        'group' => 'clock',
        'label' => 'للعميل والمستقل: تذكير بالتقييم بعد اكتمال المشروع',
        'text' => 'اكتمل المشروع {number}. قيّم تجربتك (اختياري) لتساعد غيرك على الاختيار.',
        'vars' => ['number' => 'رقم العقد'],
    ],

    // StageClock — due dates and lateness
    'clock.due_reminder' => [
        'group' => 'clock',
        'label' => 'للمستقل: تذكير قبل موعد تسليم المرحلة',
        'text' => 'تذكير: موعد تسليم المرحلة «{stage}» من {number} هو {date}.',
        'vars' => ['number' => 'رقم العقد', 'stage' => 'اسم المرحلة', 'date' => 'موعد التسليم (تاريخ وساعة)'],
    ],
    'clock.late_freelancer' => [
        'group' => 'clock',
        'label' => 'للمستقل: عند تأخر المرحلة عن موعدها',
        'text' => 'تأخرت المرحلة «{stage}» من {number} عن موعدها. سلّمها أو أوقف العدّاد إن كنت تنتظر شيئًا من العميل.',
        'vars' => ['number' => 'رقم العقد', 'stage' => 'اسم المرحلة'],
    ],
    'clock.late_client' => [
        'group' => 'clock',
        'label' => 'للعميل: عند تأخر المستقل في تسليم المرحلة',
        'text' => 'تأخر المستقل في تسليم المرحلة «{stage}» من {number}. بعد {days} أيام تأخير يحق لك طلب تغيير المستقل.',
        'vars' => ['number' => 'رقم العقد', 'stage' => 'اسم المرحلة', 'days' => 'أيام التأخير التي تسمح بطلب تغيير المستقل'],
    ],
    'clock.admin_late' => [
        'group' => 'admin',
        'label' => 'للإدارة: عند تأخر المرحلة عن موعدها',
        'text' => 'تأخرت المرحلة «{stage}» في {number}',
        'vars' => ['number' => 'رقم العقد', 'stage' => 'اسم المرحلة'],
    ],
    'clock.late_escalated_client' => [
        'group' => 'clock',
        'label' => 'للعميل: بعد أيام التأخير التي تسمح بطلب تغيير المستقل',
        'text' => 'مرّت {days} أيام على تأخر المرحلة «{stage}» من {number}. يحق لك طلب تغيير المستقل، وسيتواصل معك فريق نفّذها.',
        'vars' => ['number' => 'رقم العقد', 'stage' => 'اسم المرحلة', 'days' => 'أيام التأخير التي تسمح بطلب تغيير المستقل'],
    ],
    'clock.admin_late_escalated' => [
        'group' => 'admin',
        'label' => 'للإدارة: بعد أيام التأخير التي تسمح للعميل بطلب تغيير المستقل',
        'text' => '{days} أيام تأخير في {number} (المرحلة «{stage}»): يحق للعميل طلب تغيير المستقل',
        'vars' => ['number' => 'رقم العقد', 'stage' => 'اسم المرحلة', 'days' => 'أيام التأخير التي تسمح بطلب تغيير المستقل'],
    ],

    // StageClock — reviews
    'clock.review_reminder' => [
        'group' => 'clock',
        'label' => 'للعميل: تذكير بمراجعة تسليم ينتظره',
        'text' => 'تذكير: تسليم المرحلة «{stage}» من {number} بانتظار مراجعتك حتى {date}.',
        'vars' => ['number' => 'رقم العقد', 'stage' => 'اسم المرحلة', 'date' => 'آخر موعد للمراجعة'],
    ],
    'clock.admin_review_overdue' => [
        'group' => 'admin',
        'label' => 'للإدارة: عند انتهاء مهلة مراجعة العميل دون رد',
        'text' => 'انتهت مهلة مراجعة العميل في {number} (المرحلة «{stage}») دون رد',
        'vars' => ['number' => 'رقم العقد', 'stage' => 'اسم المرحلة'],
    ],

    // DisputeService
    'disputes.opened' => [
        'group' => 'disputes',
        'label' => 'للطرف الآخر: عند فتح خلاف على المشروع',
        'text' => 'فُتح خلاف على المشروع {number} ({reason}). أمامك {days} أيام للرد وإرفاق أدلتك، ثم تقرر الإدارة.',
        'vars' => ['number' => 'رقم العقد', 'reason' => 'سبب الخلاف', 'days' => 'مهلة الرد بالأيام'],
    ],
    'disputes.admin_opened' => [
        'group' => 'admin',
        'label' => 'للإدارة: عند فتح خلاف جديد',
        'text' => 'خلاف جديد على {number}: {reason}',
        'vars' => ['number' => 'رقم العقد', 'reason' => 'سبب الخلاف'],
    ],
    'disputes.responded' => [
        'group' => 'disputes',
        'label' => 'لفاتح الخلاف: عند رد الطرف الآخر',
        'text' => 'ردّ الطرف الآخر على الخلاف في {number}. القرار الآن لدى الإدارة.',
        'vars' => ['number' => 'رقم العقد'],
    ],
    'disputes.admin_responded' => [
        'group' => 'admin',
        'label' => 'للإدارة: عند الرد على الخلاف وجاهزيته للقرار',
        'text' => 'رُدّ على الخلاف في {number}: جاهز لقرارك',
        'vars' => ['number' => 'رقم العقد'],
    ],
    'disputes.withdrawn' => [
        'group' => 'disputes',
        'label' => 'للطرف الآخر: عند سحب الخلاف',
        'text' => 'سُحب الخلاف على المشروع {number}.',
        'vars' => ['number' => 'رقم العقد'],
    ],
    'disputes.decided' => [
        'group' => 'disputes',
        'label' => 'للعميل والمستقل: عند قرار الإدارة في الخلاف',
        'text' => 'قرار الإدارة في خلاف المشروع {number}: {decision} — {note}',
        'vars' => ['number' => 'رقم العقد', 'decision' => 'القرار', 'note' => 'سبب القرار المكتوب'],
    ],
    'disputes.admin_proposal' => [
        'group' => 'admin',
        'label' => 'لمن يحق له الاعتماد: عند اقتراح قرار مالي في خلاف',
        'text' => 'قرار مقترح في خلاف {number} بانتظار اعتمادك: {decision}',
        'vars' => ['number' => 'رقم العقد', 'decision' => 'القرار المقترح'],
    ],
    'disputes.admin_respond_overdue' => [
        'group' => 'admin',
        'label' => 'للإدارة: عند انتهاء مهلة الرد على الخلاف دون رد',
        'text' => 'انتهت مهلة الرد على الخلاف في {number} دون رد: قرر بما لديك',
        'vars' => ['number' => 'رقم العقد'],
    ],

    // FreelancerChangeService
    'disputes.admin_change_requested' => [
        'group' => 'admin',
        'label' => 'للإدارة: عند طلب تغيير المستقل (طلب العميل / انسحاب المستقل / قرار الإدارة)',
        'text' => '{kind} لتغيير المستقل في {number}: {reason}',
        'vars' => ['number' => 'رقم العقد', 'kind' => 'نوع الطلب (طلب العميل، انسحاب المستقل…)', 'reason' => 'سبب الطلب'],
    ],
    'disputes.change_requested' => [
        'group' => 'disputes',
        'label' => 'للطرف الآخر: عند طلب تغيير المستقل',
        'text' => '{kind} لتغيير المستقل في المشروع {number}، والقرار لدى الإدارة.',
        'vars' => ['number' => 'رقم العقد', 'kind' => 'نوع الطلب (طلب العميل، انسحاب المستقل…)'],
    ],
    'disputes.change_rejected' => [
        'group' => 'disputes',
        'label' => 'للعميل والمستقل: عند رفض الإدارة تغيير المستقل',
        'text' => 'رفضت الإدارة تغيير المستقل في {number}: {note}',
        'vars' => ['number' => 'رقم العقد', 'note' => 'سبب الرفض'],
    ],
    'disputes.change_applied_client' => [
        'group' => 'disputes',
        'label' => 'للعميل: عند اعتماد تغيير المستقل ونشر الجزء المتبقي',
        'text' => 'تغيّر المستقل في {number}. نُشر الجزء المتبقي في {continuation} لعروض جديدة، وما أُعيد إليك أُضيف لرصيدك.',
        'vars' => ['number' => 'رقم العقد', 'continuation' => 'رقم الطلب الجديد للجزء المتبقي'],
    ],
    'disputes.change_applied_freelancer' => [
        'group' => 'disputes',
        'label' => 'للمستقل المغادر: عند اعتماد الإدارة تغيير المستقل',
        'text' => 'اعتمدت الإدارة تغيير المستقل في {number}: {note}',
        'vars' => ['number' => 'رقم العقد', 'note' => 'سبب القرار'],
    ],
];
