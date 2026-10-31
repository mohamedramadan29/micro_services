<?php

/*
 * Notification texts of «شاركنا اقتراحك» (client file «نظام الاقتراحات»). The sender hears about the
 * receipt and the statuses «مخطط للتنفيذ» / «تم التنفيذ» / «مغلق» only. See App\Services\Notifications\MessageTemplates.
 */
return [
    'suggestions.received' => [
        'group' => 'suggestions',
        'label' => 'لصاحب الاقتراح: تم استلام اقتراحه',
        'text' => 'شكرًا لمشاركتك. وصل اقتراحك «{title}» بنجاح، رقم المتابعة {number}',
        'vars' => ['title' => 'عنوان الاقتراح', 'number' => 'رقم المتابعة'],
    ],
    'suggestions.planned' => [
        'group' => 'suggestions',
        'label' => 'لصاحب الاقتراح: صار اقتراحه «مخطط للتنفيذ»',
        'text' => 'اقتراحك {number} «{title}» اعتمدناه مبدئيًا وصار مخططًا للتنفيذ. {reason}',
        'vars' => ['title' => 'عنوان الاقتراح', 'number' => 'رقم المتابعة', 'reason' => 'رد الفريق (إن وُجد)'],
    ],
    'suggestions.done' => [
        'group' => 'suggestions',
        'label' => 'لصاحب الاقتراح: تم تنفيذ اقتراحه',
        'text' => 'تم تنفيذ اقتراحك {number} «{title}» في نفّذها، شكرًا لأنك ساهمت في تطويرها. {reason}',
        'vars' => ['title' => 'عنوان الاقتراح', 'number' => 'رقم المتابعة', 'reason' => 'رد الفريق (إن وُجد)'],
    ],
    'suggestions.closed' => [
        'group' => 'suggestions',
        'label' => 'لصاحب الاقتراح: أُغلق اقتراحه',
        'text' => 'أغلقنا اقتراحك {number} «{title}». {reason}',
        'vars' => ['title' => 'عنوان الاقتراح', 'number' => 'رقم المتابعة', 'reason' => 'رد الفريق (إن وُجد)'],
    ],
    'admin.suggestion_new' => [
        'group' => 'admin',
        'label' => 'للإدارة: اقتراح جديد',
        'text' => 'اقتراح جديد {number} ({category}) من {name}: {title}',
        'vars' => ['number' => 'رقم المتابعة', 'category' => 'نوع الاقتراح', 'name' => 'المرسل', 'title' => 'عنوان الاقتراح'],
    ],
    'admin.suggestion_assigned' => [
        'group' => 'admin',
        'label' => 'للأدمن المسؤول: أُسند إليه اقتراح',
        'text' => 'أُسند إليك الاقتراح {number}: {title}',
        'vars' => ['number' => 'رقم المتابعة', 'title' => 'عنوان الاقتراح'],
    ],
];
