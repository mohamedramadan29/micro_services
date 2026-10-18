<?php

// «مشروعك ما بيوقف»: choosing the freelancer, the agreement, stage funding/delivery/review,
// addenda and the admin's contract tools. Used through App\Support\Msg::t().

$number = ['number' => 'رقم المشروع (CNT-…)'];
$stage = ['stage' => 'اسم المرحلة'];

return [
    // Choosing the freelancer (StagedProjectService)
    'contracts.offer_chosen' => [
        'group' => 'contracts',
        'label' => 'للمستقل: اختار العميل عرضه على مشروع بمراحل',
        'text' => 'اختار العميل عرضك على المشروع {number}. الخطوة التالية: اقتراح الاتفاق ومراحل الدفع والتسليم.',
        'vars' => ['number' => 'رقم الطلب (REQ-…)'],
    ],

    // Agreement (AgreementService)
    'contracts.agreement_proposed' => [
        'group' => 'contracts',
        'label' => 'للعميل: اقترح المستقل الاتفاق (أو نسخة جديدة منه)',
        'text' => 'اقترح المستقل اتفاق مشروعك {number} (نسخة {version}). راجعه ووافق أو اطلب تعديلًا.',
        'vars' => $number + ['version' => 'رقم نسخة الاتفاق'],
    ],
    'contracts.agreement_changes_requested' => [
        'group' => 'contracts',
        'label' => 'للمستقل: طلب العميل تعديل الاتفاق',
        'text' => 'طلب العميل تعديل اتفاق المشروع {number}: {note}',
        'vars' => $number + ['note' => 'ملاحظة العميل'],
    ],
    'contracts.agreement_approved' => [
        'group' => 'contracts',
        'label' => 'للمستقل: وافق العميل على الاتفاق',
        'text' => 'وافق العميل على اتفاق المشروع {number}. يبدأ العمل بعد تمويل المرحلة الأولى.',
        'vars' => $number,
    ],

    // Stages (StageWorkflow)
    'contracts.stage_awaiting_funding_client' => [
        'group' => 'contracts',
        'label' => 'للعميل: المرحلة التالية جاهزة وتحتاج تمويلًا',
        'text' => 'المرحلة «{stage}» من {number} جاهزة للبدء. موّلها ({amount}) ليبدأ المستقل العمل عليها.',
        'vars' => $stage + $number + ['amount' => 'مبلغ المرحلة'],
    ],
    'contracts.stage_awaiting_funding_freelancer' => [
        'group' => 'contracts',
        'label' => 'للمستقل: المرحلة التالية بانتظار تمويل العميل',
        'text' => 'المرحلة «{stage}» من {number} بانتظار تمويل العميل، وأُرسل له تذكير.',
        'vars' => $stage + $number,
    ],
    'contracts.stage_started' => [
        'group' => 'contracts',
        'label' => 'للمستقل: بدأت مرحلة مموّلة',
        'text' => 'بدأت المرحلة «{stage}» من {number} (مموّلة). موعد التسليم {date}.',
        'vars' => $stage + $number + ['date' => 'موعد التسليم'],
    ],
    'contracts.stage_delivered' => [
        'group' => 'contracts',
        'label' => 'للعميل: سلّم المستقل مرحلة',
        'text' => 'سلّم المستقل المرحلة «{stage}» من {number}. راجعها خلال {days} أيام: اعتمدها أو اطلب تعديلًا.',
        'vars' => $stage + $number + ['days' => 'مهلة المراجعة بالأيام'],
    ],
    'contracts.stage_approved' => [
        'group' => 'contracts',
        'label' => 'للمستقل: اعتمد العميل مرحلة (والمشروع مستمر)',
        'text' => 'اعتمد العميل المرحلة «{stage}» من {number}، وأُضيف {amount} إلى رصيدك.',
        'vars' => $stage + $number + ['amount' => 'المبلغ المضاف لرصيد المستقل'],
    ],
    'contracts.stage_approved_completed' => [
        'group' => 'contracts',
        'label' => 'للمستقل: اعتمد العميل آخر مرحلة واكتمل المشروع',
        'text' => 'اعتمد العميل المرحلة «{stage}» من {number}، وأُضيف {amount} إلى رصيدك. اكتمل المشروع.',
        'vars' => $stage + $number + ['amount' => 'المبلغ المضاف لرصيد المستقل'],
    ],
    'contracts.stage_revision_requested' => [
        'group' => 'contracts',
        'label' => 'للمستقل: طلب العميل تعديلًا على مرحلة مسلّمة',
        'text' => 'طلب العميل تعديلًا على المرحلة «{stage}» من {number}: {note}',
        'vars' => $stage + $number + ['note' => 'ملاحظة العميل'],
    ],

    // Funding (ContractLedger)
    'contracts.funding_leftover' => [
        'group' => 'contracts',
        'label' => 'للعميل: وصل الدفع لكن جزءًا منه لمرحلة لم تعد بانتظار التمويل فأُضيف لرصيده',
        'text' => 'وصل دفعك لـ {number}، لكن {amount} منه لمرحلة لم تعد بانتظار التمويل، فأُضيف إلى رصيدك.',
        'vars' => $number + ['amount' => 'المبلغ المضاف للرصيد'],
    ],
    'contracts.funding_failed' => [
        'group' => 'contracts',
        'label' => 'للعميل: لم يكتمل دفع تمويل المراحل عبر EasyKash',
        'text' => 'لم يكتمل دفع تمويل مراحل {number}. لم يُخصم شيء من نفّذها، ويمكنك المحاولة مرة أخرى من صفحة المشروع.',
        'vars' => $number,
    ],
    'contracts.stages_funded_client' => [
        'group' => 'contracts',
        'label' => 'للعميل: تم تمويل مرحلة أو أكثر',
        'text' => 'تم تمويل {stages} من {number} ({total}). يبقى المبلغ لدى نفّذها حتى تعتمد المرحلة.',
        'vars' => $number + ['stages' => 'أسماء المراحل المموّلة (بين «»)', 'total' => 'إجمالي التمويل'],
    ],
    'contracts.stages_funded_ahead' => [
        'group' => 'contracts',
        'label' => 'للمستقل: موّل العميل مراحل قادمة مقدمًا',
        'text' => 'موّل العميل مقدمًا {stages} من {number}.',
        'vars' => $number + ['stages' => 'أسماء المراحل (بين «»)'],
    ],

    // Addenda (AddendumService)
    'contracts.addendum_proposed' => [
        'group' => 'contracts',
        'label' => 'للطرف الآخر: اقتُرح ملحق على المشروع',
        'text' => 'اقتُرح ملحق على المشروع {number} ({lines}). راجعه ووافق أو ارفض.',
        'vars' => $number + ['lines' => 'بنود الملحق'],
    ],
    'contracts.addendum_approved' => [
        'group' => 'contracts',
        'label' => 'لمقترح الملحق: وافق الطرف الآخر وطُبّق (بدون ملاحظة)',
        'text' => 'وافق الطرف الآخر على الملحق وطُبّق على المشروع {number}.',
        'vars' => $number,
    ],
    'contracts.addendum_approved_note' => [
        'group' => 'contracts',
        'label' => 'لمقترح الملحق: وافق الطرف الآخر وطُبّق (مع ملاحظة)',
        'text' => 'وافق الطرف الآخر على الملحق وطُبّق على المشروع {number}: {note}',
        'vars' => $number + ['note' => 'ملاحظة الطرف الآخر'],
    ],
    'contracts.addendum_rejected' => [
        'group' => 'contracts',
        'label' => 'لمقترح الملحق: رُفض الملحق (بدون ملاحظة)',
        'text' => 'رُفض الملحق المقترح على المشروع {number}.',
        'vars' => $number,
    ],
    'contracts.addendum_rejected_note' => [
        'group' => 'contracts',
        'label' => 'لمقترح الملحق: رُفض الملحق (مع ملاحظة)',
        'text' => 'رُفض الملحق المقترح على المشروع {number}: {note}',
        'vars' => $number + ['note' => 'سبب الرفض'],
    ],

    // Admin tools (ContractAdminTools) — sent to the client and the freelancer
    'contracts.admin_extended_deadline' => [
        'group' => 'contracts',
        'label' => 'للعميل والمستقل: مدّت الإدارة موعد مرحلة',
        'text' => 'مدّت إدارة نفّذها موعد المرحلة «{stage}» من {number} إلى {date}: {reason}',
        'vars' => $stage + $number + ['date' => 'الموعد الجديد', 'reason' => 'سبب الإدارة'],
    ],
    'contracts.admin_approved_stage' => [
        'group' => 'contracts',
        'label' => 'للعميل والمستقل: اعتمدت الإدارة مرحلة بدل العميل',
        'text' => 'اعتمدت إدارة نفّذها المرحلة «{stage}» من {number} بعد مراجعة التسليم: {reason}',
        'vars' => $stage + $number + ['reason' => 'سبب الإدارة'],
    ],
    'contracts.admin_ended_pause_extended' => [
        'group' => 'contracts',
        'label' => 'للعميل والمستقل: أنهت الإدارة إيقاف العدّاد مع تمديد الموعد',
        'text' => 'أنهت إدارة نفّذها إيقاف العدّاد في {number} مع تمديد الموعد: {reason}',
        'vars' => $number + ['reason' => 'سبب الإدارة'],
    ],
    'contracts.admin_ended_pause' => [
        'group' => 'contracts',
        'label' => 'للعميل والمستقل: أنهت الإدارة إيقاف العدّاد دون تمديد',
        'text' => 'أنهت إدارة نفّذها إيقاف العدّاد في {number} دون تمديد: {reason}',
        'vars' => $number + ['reason' => 'سبب الإدارة'],
    ],
    'contracts.admin_reply_requested' => [
        'group' => 'contracts',
        'label' => 'للعميل والمستقل: تطلب الإدارة ردهما على سؤال',
        'text' => 'تطلب إدارة نفّذها ردك على سؤال في المشروع {number} حتى {date}: {question}',
        'vars' => $number + ['date' => 'آخر موعد للرد', 'question' => 'سؤال الإدارة'],
    ],
    'contracts.admin_reply_from_client' => [
        'group' => 'admin',
        'label' => 'للإدارة: ردّ العميل على طلب الرد',
        'text' => 'ردّ العميل على طلبكم في {number}',
        'vars' => $number,
    ],
    'contracts.admin_reply_from_freelancer' => [
        'group' => 'admin',
        'label' => 'للإدارة: ردّ المستقل على طلب الرد',
        'text' => 'ردّ المستقل على طلبكم في {number}',
        'vars' => $number,
    ],
    'contracts.admin_reply_overdue' => [
        'group' => 'admin',
        'label' => 'للإدارة: انتهت مهلة الرد ولم يرد أحد الطرفين أو كلاهما',
        'text' => 'انتهت مهلة الرد في {number} ولم يرد {missing}',
        'vars' => $number + ['missing' => 'من لم يرد (العميل / المستقل / كلاهما)'],
    ],
];
