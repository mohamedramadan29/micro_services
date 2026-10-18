<?php

namespace App\Services\Landing;

/**
 * The section types a landing page is built from. Each has its fields,
 * optionally a list of items (cards, steps, questions…) and the defaults a
 * new section starts with. Every type also gets the STYLE fields.
 * Field types: see App\Services\Content\Fields.
 */
class LandingSections
{
    public const STYLE = [
        'anchor' => ['type' => 'text', 'label' => 'معرّف القسم للروابط', 'help' => 'مثال: steps — ثم اجعل رابط أي زر #steps لينزل لهذا القسم'],
        'bg_color' => ['type' => 'color', 'label' => 'لون الخلفية'],
        'bg_image' => ['type' => 'background', 'label' => 'صورة الخلفية'],
        'text_color' => ['type' => 'color', 'label' => 'لون النصوص'],
        'accent_color' => ['type' => 'color', 'label' => 'لون الأزرار والأرقام', 'help' => 'فارغ = لون الهوية'],
    ];

    private const HEAD = [
        'badge' => ['type' => 'text', 'label' => 'الشارة الصغيرة'],
        'title' => ['type' => 'text', 'label' => 'العنوان'],
        'subtitle' => ['type' => 'textarea', 'label' => 'الوصف'],
    ];

    public static function all(): array
    {
        return [
            'hero' => [
                'label' => 'الواجهة الرئيسية (Hero)',
                'icon' => 'fa-star',
                'fields' => [
                    'badge' => self::HEAD['badge'],
                    'title' => ['type' => 'text', 'label' => 'العنوان الرئيسي', 'required' => true],
                    'subtitle' => self::HEAD['subtitle'],
                    'button_text' => ['type' => 'text', 'label' => 'نص الزر الأساسي'],
                    'button_link' => ['type' => 'link', 'label' => 'رابط الزر الأساسي'],
                    'button2_text' => ['type' => 'text', 'label' => 'نص الزر الثانوي'],
                    'button2_link' => ['type' => 'link', 'label' => 'رابط الزر الثانوي'],
                    'image' => ['type' => 'image', 'label' => 'صورة جانبية'],
                ],
                'defaults' => ['title' => 'عنوان واضح يشرح القيمة', 'subtitle' => 'سطر يوضح لمن هذه الصفحة وماذا سيحصل عليه الزائر.', 'button_text' => 'ابدأ الآن', 'button_link' => '/request'],
            ],
            'cards' => [
                'label' => 'بطاقات المسارات',
                'icon' => 'fa-th-large',
                'fields' => self::HEAD + ['footnote' => ['type' => 'text', 'label' => 'سطر أسفل البطاقات']],
                'items' => [
                    'label' => 'بطاقة',
                    'max' => 6,
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'العنوان', 'required' => true],
                        'description' => ['type' => 'textarea', 'label' => 'الوصف'],
                        'icon' => ['type' => 'icon', 'label' => 'الأيقونة', 'help' => 'مثل bi-search'],
                        'image' => ['type' => 'image', 'label' => 'صورة بدل الأيقونة'],
                        'button_text' => ['type' => 'text', 'label' => 'نص الزر'],
                        'link' => ['type' => 'link', 'label' => 'الرابط', 'help' => 'فارغ = «قريبًا»'],
                        'background_color' => ['type' => 'color', 'label' => 'لون البطاقة'],
                    ],
                ],
                'defaults' => ['title' => 'اختر طريقك'],
                'default_items' => [['title' => 'بطاقة جديدة', 'button_text' => 'ابدأ', 'link' => '/request', 'icon' => 'bi-plus-lg']],
            ],
            'steps' => [
                'label' => 'خطوات مرقّمة',
                'icon' => 'fa-list-ol',
                'fields' => self::HEAD,
                'items' => [
                    'label' => 'خطوة',
                    'max' => 8,
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => 'الخطوة', 'required' => true],
                        'description' => ['type' => 'textarea', 'label' => 'الشرح'],
                        'icon' => ['type' => 'icon', 'label' => 'الأيقونة (اختياري)'],
                    ],
                ],
                'defaults' => ['title' => 'كيف نعمل؟'],
                'default_items' => [['title' => 'الخطوة الأولى'], ['title' => 'الخطوة الثانية'], ['title' => 'الخطوة الثالثة']],
            ],
            'features' => [
                'label' => 'مميزات بأيقونات',
                'icon' => 'fa-check-square',
                'fields' => self::HEAD,
                'items' => [
                    'label' => 'ميزة',
                    'max' => 12,
                    'fields' => [
                        'icon' => ['type' => 'icon', 'label' => 'الأيقونة'],
                        'title' => ['type' => 'text', 'label' => 'العنوان', 'required' => true],
                        'description' => ['type' => 'textarea', 'label' => 'الوصف'],
                    ],
                ],
                'defaults' => ['title' => 'لماذا نفّذها؟'],
                'default_items' => [['icon' => 'bi-shield-check', 'title' => 'ميزة أولى'], ['icon' => 'bi-lightning', 'title' => 'ميزة ثانية'], ['icon' => 'bi-people', 'title' => 'ميزة ثالثة']],
            ],
            'text_image' => [
                'label' => 'نص مع صورة',
                'icon' => 'fa-image',
                'fields' => [
                    'badge' => self::HEAD['badge'],
                    'title' => ['type' => 'text', 'label' => 'العنوان', 'required' => true],
                    'body' => ['type' => 'textarea', 'label' => 'النص'],
                    'image' => ['type' => 'image', 'label' => 'الصورة'],
                    'image_side' => ['type' => 'select', 'label' => 'مكان الصورة', 'options' => ['start' => 'يمين النص', 'end' => 'يسار النص']],
                    'button_text' => ['type' => 'text', 'label' => 'نص الزر'],
                    'button_link' => ['type' => 'link', 'label' => 'رابط الزر'],
                ],
                'defaults' => ['title' => 'عنوان القسم', 'body' => 'اكتب هنا شرحًا مختصرًا.', 'image_side' => 'end'],
            ],
            'stats' => [
                'label' => 'أرقام',
                'icon' => 'fa-chart-bar',
                'fields' => ['title' => self::HEAD['title']],
                'items' => [
                    'label' => 'رقم',
                    'max' => 6,
                    'fields' => [
                        'value' => ['type' => 'text', 'label' => 'الرقم', 'required' => true, 'help' => 'اكتب أرقامًا حقيقية فقط'],
                        'label' => ['type' => 'text', 'label' => 'الوصف', 'required' => true],
                    ],
                ],
                'defaults' => [],
                'default_items' => [['value' => '0', 'label' => 'وصف الرقم']],
            ],
            'testimonials' => [
                'label' => 'آراء العملاء',
                'icon' => 'fa-comment',
                'fields' => self::HEAD,
                'items' => [
                    'label' => 'رأي',
                    'max' => 9,
                    'fields' => [
                        'quote' => ['type' => 'textarea', 'label' => 'الرأي', 'required' => true],
                        'name' => ['type' => 'text', 'label' => 'الاسم', 'required' => true],
                        'role' => ['type' => 'text', 'label' => 'الصفة'],
                        'image' => ['type' => 'image', 'label' => 'الصورة'],
                    ],
                ],
                'defaults' => ['title' => 'ماذا يقول عملاؤنا'],
                'default_items' => [],
            ],
            'faq' => [
                'label' => 'أسئلة شائعة',
                'icon' => 'fa-question-circle',
                'fields' => self::HEAD,
                'items' => [
                    'label' => 'سؤال',
                    'max' => 20,
                    'fields' => [
                        'question' => ['type' => 'text', 'label' => 'السؤال', 'required' => true],
                        'answer' => ['type' => 'textarea', 'label' => 'الإجابة', 'required' => true],
                    ],
                ],
                'defaults' => ['title' => 'أسئلة شائعة'],
                'default_items' => [['question' => 'سؤال؟', 'answer' => 'الإجابة.']],
            ],
            'cta' => [
                'label' => 'دعوة لاتخاذ إجراء',
                'icon' => 'fa-bullhorn',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'العنوان', 'required' => true],
                    'subtitle' => self::HEAD['subtitle'],
                    'button_text' => ['type' => 'text', 'label' => 'نص الزر الأساسي'],
                    'button_link' => ['type' => 'link', 'label' => 'رابط الزر الأساسي'],
                    'button2_text' => ['type' => 'text', 'label' => 'نص الزر الثانوي'],
                    'button2_link' => ['type' => 'link', 'label' => 'رابط الزر الثانوي'],
                ],
                'defaults' => ['title' => 'جاهز تبدأ؟', 'button_text' => 'ابدأ الآن', 'button_link' => '/request'],
            ],
        ];
    }

    public static function exists(string $type): bool
    {
        return array_key_exists($type, self::all());
    }

    public static function get(string $type): array
    {
        return self::all()[$type] ?? throw new \InvalidArgumentException('Unknown landing section: ' . $type);
    }

    /** Section fields + the shared style fields. */
    public static function fields(string $type): array
    {
        return self::get($type)['fields'] + self::STYLE;
    }

    /** The page the starter template builds: the client's reference image. */
    public static function starter(): array
    {
        return [
            ['type' => 'hero', 'fields' => [
                'title' => 'مشروعك له طريق واضح من الفكرة للتنفيذ.',
                'subtitle' => 'اختار ما تحتاجه، وسنرشدك إلى الخطوة المناسبة.',
                'button_text' => 'ابدأ الآن', 'button_link' => '#paths',
                'button2_text' => 'كيف نعمل؟', 'button2_link' => '#steps',
            ]],
            ['type' => 'cards', 'fields' => ['anchor' => 'paths', 'title' => 'اختر نقطة البداية'], 'items' => [
                ['title' => 'اطلب عرضاً لمشروعك', 'description' => 'اشرح احتياجك واستقبل عروض المستقلين.', 'button_text' => 'اطلب الآن', 'link' => '/request', 'icon' => 'bi-send', 'background_color' => '#EAF2FB'],
                ['title' => 'شخّص مشروعك مجاناً', 'description' => 'اكتشف فرص التحسين والخطوات المناسبة.', 'button_text' => 'ابدأ الفحص', 'link' => '/site-test', 'icon' => 'bi-search', 'background_color' => '#E8F5EF'],
                ['title' => 'مشروعك ما بيوقف', 'description' => 'دفعات ومراحل تحفظ حق العميل والمستقل.', 'button_text' => 'ابدأ مشروعك', 'link' => '/staged-project', 'icon' => 'bi-shield-check', 'background_color' => '#FFF4DD'],
            ]],
            ['type' => 'steps', 'fields' => ['anchor' => 'steps', 'title' => 'أربع خطوات تفصلك عن بداية التنفيذ'], 'items' => [
                ['title' => 'اختر الخدمة'],
                ['title' => 'اشرح احتياجك'],
                ['title' => 'راجع العروض'],
                ['title' => 'ابدأ باتفاق واضح'],
            ]],
            ['type' => 'cta', 'fields' => [
                'title' => 'جاهز تبدأ مشروعك؟',
                'subtitle' => 'أرسل طلبك الآن، والمستقلون المناسبون يرسلون عروضهم.',
                'button_text' => 'اطلب عرضك', 'button_link' => '/request',
                'button2_text' => 'تصفّح الخدمات', 'button2_link' => '/services',
            ]],
        ];
    }
}
