<?php

/*
 * Notification texts of «نفّذها للأعمال» (client file «قسم الشركات»). See App\Services\Notifications\MessageTemplates.
 */
return [
    'business.received' => ['group' => 'business', 'label' => 'للشركة: وصل طلبها',
        'text' => 'وصل طلبك {number} لفريق نفّذها للأعمال، وسنتواصل معك قريبًا.', 'vars' => ['number' => 'رقم الطلب']],
    'business.quote_sent' => ['group' => 'business', 'label' => 'للشركة: أُرسل عرض السعر',
        'text' => 'عرض سعر لطلبك {number}: {amount}. راجعه واعتمده من حسابك.', 'vars' => ['number' => 'رقم الطلب', 'amount' => 'المبلغ']],
    'business.paid' => ['group' => 'business', 'label' => 'للشركة: تم تسجيل الدفع',
        'text' => 'تم تسجيل دفع طلبك {number}، شكرًا لك.', 'vars' => ['number' => 'رقم الطلب']],
    'business.project_created' => ['group' => 'business', 'label' => 'للشركة: بدأ المشروع',
        'text' => 'بدأ مشروعك {project} «{title}» وتستطيع متابعته من حسابك.', 'vars' => ['project' => 'رقم المشروع', 'title' => 'عنوان المشروع', 'number' => 'رقم الطلب']],
    'business.delivery_ready' => ['group' => 'business', 'label' => 'للشركة: نسخة جاهزة للاعتماد',
        'text' => '«{title}» (نسخة {version}) جاهزة لمراجعتك واعتمادها في مشروع {number}.', 'vars' => ['title' => 'اسم المخرج', 'version' => 'رقم النسخة', 'number' => 'رقم المشروع']],
    'business.project_completed' => ['group' => 'business', 'label' => 'للشركة: اكتمل المشروع',
        'text' => 'اكتمل مشروعك {number} «{title}». شكرًا لثقتك في نفّذها.', 'vars' => ['number' => 'رقم المشروع', 'title' => 'عنوان المشروع']],
    'business.admin_new' => ['group' => 'admin', 'label' => 'للفريق: طلب شركة جديد',
        'text' => 'طلب شركة جديد {number} ({source}) من {name}', 'vars' => ['number' => 'رقم الطلب', 'source' => 'المسار', 'name' => 'الشركة أو المرسل']],
    'business.admin_assigned' => ['group' => 'admin', 'label' => 'للموظف: أُسند إليه طلب شركة',
        'text' => 'أُسند إليك طلب الشركة {number} ({name})', 'vars' => ['number' => 'رقم الطلب', 'name' => 'الشركة']],
    'business.admin_quote_accepted' => ['group' => 'admin', 'label' => 'للفريق: الشركة قبلت عرض السعر',
        'text' => '{name} قبل عرض السعر للطلب {number}؛ حوّله لمشروع.', 'vars' => ['number' => 'رقم الطلب', 'name' => 'الشركة']],
    'business.admin_quote_rejected' => ['group' => 'admin', 'label' => 'للفريق: الشركة رفضت عرض السعر',
        'text' => '{name} رفض عرض السعر للطلب {number}.', 'vars' => ['number' => 'رقم الطلب', 'name' => 'الشركة']],
    'business.admin_paid' => ['group' => 'admin', 'label' => 'للفريق: دفعت الشركة إلكترونيًا',
        'text' => 'دفع {name} قيمة الطلب {number} إلكترونيًا.', 'vars' => ['number' => 'رقم الطلب', 'name' => 'الشركة']],
    'business.admin_task_assigned' => ['group' => 'admin', 'label' => 'للموظف: أُسندت إليه مهمة',
        'text' => 'أُسندت إليك مهمة «{title}» في مشروع {project}', 'vars' => ['title' => 'المهمة', 'project' => 'رقم المشروع']],
    'business.admin_approved' => ['group' => 'admin', 'label' => 'للفريق: الشركة اعتمدت نسخة',
        'text' => '{name} اعتمد «{title}» (نسخة {version}) في مشروع {project}', 'vars' => ['name' => 'العميل', 'title' => 'المخرج', 'version' => 'النسخة', 'project' => 'رقم المشروع']],
    'business.admin_changes' => ['group' => 'admin', 'label' => 'للفريق: الشركة طلبت تعديلًا',
        'text' => '{name} طلب تعديل «{title}» (نسخة {version}) في مشروع {project}', 'vars' => ['name' => 'العميل', 'title' => 'المخرج', 'version' => 'النسخة', 'project' => 'رقم المشروع']],
];
