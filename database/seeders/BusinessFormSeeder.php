<?php

namespace Database\Seeders;

use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The companies' guided request form («حدّد طلبك خطوة بخطوة», client file «قسم الشركات» page 7). It is an ordinary
 * form of «نماذج الخدمات» attached to a HIDDEN group/service (status 0: never on /services, the request page or
 * freelancers' specializations), so the admin edits questions, choices, conditions and steps in the existing
 * builder. Each section = one step on the page. Company name, size, phone and email are fixed fields of the page.
 *
 * Safe to run again: an existing form (the admin's work) is never touched.
 */
class BusinessFormSeeder extends Seeder
{
    public const SLUG = 'nafizha-business-guided-form';

    public static function service(): ?SubCategory
    {
        return SubCategory::where('slug', self::SLUG)->first();
    }

    public function run(): void
    {
        DB::transaction(function () {
            $group = Category::firstOrCreate(['slug' => 'nafizha-business'], [
                'name' => 'نفّذها للأعمال (نموذج الشركات — مخفي)', 'image' => '', 'status' => 0,
            ]);
            $service = SubCategory::firstOrCreate(['slug' => self::SLUG], [
                'name' => 'طلب شركة — النموذج الموجّه', 'image' => '', 'parent_id' => $group->id, 'status' => 0,
            ]);
            if (ServiceForm::main()->where('sub_category_id', $service->id)->exists()) {
                return;
            }

            $form = ServiceForm::create(['sub_category_id' => $service->id, 'status' => ServiceForm::STATUS_PUBLISHED, 'published_at' => now()]);
            $order = 0;
            $ids = [];
            foreach ($this->steps() as $s => [$title, $description, $questions]) {
                $section = $form->sections()->create(['title' => $title, 'description' => $description, 'sort_order' => $s + 1]);
                foreach ($questions as $item) {
                    $condition = isset($item['when']) ? ['question_id' => $ids[$item['when'][0]], 'values' => (array) $item['when'][1], 'negate' => false] : null;
                    $question = $form->questions()->create([
                        'service_form_section_id' => $section->id, 'type' => $item['type'], 'label' => $item['label'],
                        'help_text' => $item['help'] ?? null, 'placeholder' => $item['placeholder'] ?? null,
                        'is_required' => $item['required'] ?? false, 'options' => $item['options'] ?? null,
                        'settings' => $item['settings'] ?? null, 'condition' => $condition, 'sort_order' => ++$order,
                    ]);
                    if (isset($item['key'])) {
                        $ids[$item['key']] = $question->id;
                    }
                }
            }
        });
    }

    private function steps(): array
    {
        $count = fn (string $label, string $unit) => ['type' => Q::TYPE_NUMBER, 'label' => $label, 'settings' => ['mode' => 'single', 'min' => 1, 'max' => 1000, 'unit' => $unit]];

        return [
            ['المشكلة والهدف', 'ما التحدي الذي تواجهه شركتك؟ وما النتيجة التي تريدها؟', [
                ['type' => Q::TYPE_LONG_TEXT, 'label' => 'ما التحدي أو ما تحتاجه؟', 'required' => true,
                    'placeholder' => 'مثال: نحتاج إنتاج 12 مقطعًا شهريًا من حلقات البودكاست ونشرها على حساباتنا', 'settings' => ['max_length' => 5000]],
                ['type' => Q::TYPE_LONG_TEXT, 'label' => 'ما النتيجة المطلوبة؟', 'placeholder' => 'مثال: زيادة المتابعين، طلبات أكثر، ظهور ثابت كل أسبوع', 'settings' => ['max_length' => 2000]],
                ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'ما الأولوية؟', 'required' => true, 'options' => ['عادي', 'خلال أسبوعين', 'عاجل']],
            ]],
            ['الخدمات والميزانية', 'اختر الخدمات؛ تظهر أسئلة كل خدمة تختارها.', [
                ['type' => Q::TYPE_MULTI_CHOICE, 'label' => 'الخدمات المطلوبة', 'required' => true, 'key' => 'services',
                    'options' => ['ريلز وفيديو قصير', 'تقطيع بودكاست', 'تصاميم ومنشورات', 'إدارة السوشيال ميديا', 'إدارة مشروع متكامل', 'تقارير وتحليلات']],
                $count('عدد المقاطع شهريًا', 'مقطع') + ['when' => ['services', 'ريلز وفيديو قصير']],
                ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'مدة الحلقة تقريبًا', 'options' => ['أقل من 30 دقيقة', '30 - 60 دقيقة', 'أكثر من ساعة'], 'when' => ['services', 'تقطيع بودكاست']],
                $count('عدد الحلقات شهريًا', 'حلقة') + ['when' => ['services', 'تقطيع بودكاست']],
                ['type' => Q::TYPE_URL, 'label' => 'رابط المادة الخام (الحلقات)', 'placeholder' => 'https://', 'help' => 'رابط Drive أو Dropbox أو يوتيوب.', 'when' => ['services', 'تقطيع بودكاست']],
                $count('عدد التصاميم شهريًا', 'تصميم') + ['when' => ['services', 'تصاميم ومنشورات']],
                ['type' => Q::TYPE_MULTI_CHOICE, 'label' => 'الحسابات المطلوب إدارتها', 'options' => ['فيسبوك', 'إنستغرام', 'تيك توك', 'يوتيوب', 'لينكد إن', 'تويتر (X)', 'سناب شات'], 'when' => ['services', 'إدارة السوشيال ميديا']],
                ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => 'نوع التعاقد', 'required' => true, 'options' => ['مشروع لمرة واحدة', 'شهري مستمر']],
                ['type' => Q::TYPE_NUMBER, 'label' => 'الميزانية المتوقعة', 'help' => 'تقديرية؛ نرسل لك عرض سعر بعد مراجعة النطاق.',
                    'settings' => ['mode' => 'range', 'min' => 100, 'max' => 10000000, 'unit' => 'ج.م']],
                ['type' => Q::TYPE_DATE, 'label' => 'موعد الانطلاق المطلوب'],
            ]],
            ['الملفات والإرسال', 'أرفق ما يساعدنا: الهوية، أمثلة، مواد خام. للملفات الكبيرة ضع رابطًا.', [
                ['type' => Q::TYPE_LONG_TEXT, 'label' => 'روابط المواد والملفات', 'placeholder' => 'رابط في كل سطر (Drive، WeTransfer، Dropbox…)', 'settings' => ['max_length' => 3000]],
                ['type' => Q::TYPE_FILE, 'label' => 'ملفات مرفقة', 'help' => 'حتى 5 ملفات، 20 ميجابايت للملف. الفيديوهات الكبيرة أرسلها برابط.',
                    'settings' => ['max_files' => 5, 'max_size_mb' => 20, 'extensions' => 'jpg,jpeg,png,webp,pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,mp3,mp4']],
            ]],
        ];
    }
}
