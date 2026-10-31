<?php

namespace Database\Seeders;

use App\Models\admin\SubCategory;
use App\Models\ServiceForm;
use App\Models\ServiceFormQuestion as Q;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Ready request forms (questions) for the 42 services of the services page (user request 2026-10-31:
 * «the site must look ready; the admin edits, adds and deletes later»).
 *
 * Each service gets one published form: a section of questions specific to the service, then the shared
 * «الميزانية والموعد» section whose answers feed the freelancers' request filters (budget, deadline, urgency,
 * experience). The description, attachments and contact details are asked by the request page itself.
 *
 * Safe to run again: a service that already has a form (published, draft or paused — the admin's work) is
 * never touched. Runs the services catalog seeder first so the services exist.
 *
 *     php artisan db:seed --class=ServiceFormsSeeder
 */
class ServiceFormsSeeder extends Seeder
{
    private const COUNTRIES = ['مصر', 'السعودية', 'الإمارات', 'الكويت', 'قطر', 'دول أخرى'];
    private const PLATFORMS = ['فيسبوك', 'إنستغرام', 'تيك توك', 'سناب شات', 'يوتيوب', 'لينكد إن', 'تويتر (X)'];
    private const SITE_PLATFORMS = ['ووردبريس', 'شوبيفاي', 'سلة', 'زد', 'برمجة خاصة', 'لا أعرف'];
    private const ONCE_OR_ONGOING = ['مرة واحدة', 'عمل شهري مستمر'];

    /** Forms created by the last run (for the console message and the tests). */
    public int $created = 0;

    public function run(): void
    {
        $this->call(ServicesCatalogSeeder::class);

        foreach ($this->definitions() as $name => [$title, $questions]) {
            $service = SubCategory::where('name', $name)->first();
            if (! $service || ServiceForm::main()->where('sub_category_id', $service->id)->exists()) {
                continue;
            }
            DB::transaction(fn () => $this->createForm($service, $title, $questions));
            $this->created++;
        }

        $this->command?->info("Request forms created: {$this->created} (services that already had a form were left as they are).");
    }

    private function createForm(SubCategory $service, string $title, array $questions): void
    {
        $form = ServiceForm::create(['sub_category_id' => $service->id, 'status' => ServiceForm::STATUS_PUBLISHED, 'published_at' => now()]);
        $sections = [
            [$title, 'أسئلة قصيرة تساعد المستقلين يفهموا طلبك ويقدّموا عروضًا دقيقة.', $questions],
            ['الميزانية والموعد', 'تقريبية؛ تقدر تتفاوض مع المستقل بعد ما توصلك العروض.', $this->shared()],
        ];

        $order = 0;
        foreach ($sections as $s => [$sectionTitle, $description, $items]) {
            $section = $form->sections()->create(['title' => $sectionTitle, 'description' => $description, 'sort_order' => $s + 1]);
            $ids = [];
            foreach ($items as $item) {
                $condition = null;
                if (isset($item['when'])) {
                    [$key, $values] = $item['when'];
                    $condition = ['question_id' => $ids[$key], 'values' => (array) $values, 'negate' => false];
                }
                $question = $form->questions()->create([
                    'service_form_section_id' => $section->id,
                    'type' => $item['type'],
                    'label' => $item['label'],
                    'help_text' => $item['help'] ?? null,
                    'placeholder' => $item['placeholder'] ?? null,
                    'is_required' => $item['required'] ?? false,
                    'options' => $item['options'] ?? null,
                    'settings' => $item['settings'] ?? null,
                    'condition' => $condition,
                    'filter_key' => $item['filter'] ?? null,
                    'sort_order' => ++$order,
                ]);
                if (isset($item['key'])) {
                    $ids[$item['key']] = $question->id;
                }
            }
        }
    }

    // ── Question builders ──────────────────────────────────────────────

    private function choice(string $label, array $options, bool $required = true, array $extra = []): array
    {
        return ['type' => Q::TYPE_SINGLE_CHOICE, 'label' => $label, 'options' => $options, 'required' => $required] + $extra;
    }

    private function multi(string $label, array $options, bool $required = true, array $extra = []): array
    {
        return ['type' => Q::TYPE_MULTI_CHOICE, 'label' => $label, 'options' => $options, 'required' => $required] + $extra;
    }

    private function text(string $label, string $placeholder = '', bool $required = false, array $extra = []): array
    {
        return ['type' => Q::TYPE_SHORT_TEXT, 'label' => $label, 'placeholder' => $placeholder ?: null, 'required' => $required] + $extra;
    }

    private function long(string $label, string $placeholder = '', bool $required = false, array $extra = []): array
    {
        return ['type' => Q::TYPE_LONG_TEXT, 'label' => $label, 'placeholder' => $placeholder ?: null, 'required' => $required,
            'settings' => ['max_length' => 2000]] + $extra;
    }

    private function yesNo(string $label, bool $required = true, array $extra = []): array
    {
        return ['type' => Q::TYPE_YES_NO, 'label' => $label, 'required' => $required] + $extra;
    }

    private function url(string $label, bool $required = false, array $extra = []): array
    {
        return ['type' => Q::TYPE_URL, 'label' => $label, 'placeholder' => 'https://', 'required' => $required] + $extra;
    }

    private function count(string $label, string $unit, int $max = 1000, bool $required = false, array $extra = []): array
    {
        return ['type' => Q::TYPE_NUMBER, 'label' => $label, 'required' => $required,
            'settings' => ['mode' => 'single', 'min' => 1, 'max' => $max, 'unit' => $unit]] + $extra;
    }

    private function files(string $label, string $help = 'صور أو ملفات توضيحية (اختياري).'): array
    {
        return ['type' => Q::TYPE_FILE, 'label' => $label, 'help' => $help, 'required' => false,
            'settings' => ['max_files' => 5, 'max_size_mb' => 10, 'extensions' => 'jpg,jpeg,png,pdf,doc,docx,zip']];
    }

    private function field(): array
    {
        return $this->text('ما مجال نشاطك؟', 'مثال: مطعم، متجر ملابس، عيادة أسنان، شركة عقارات', true);
    }

    private function countries(string $label = 'أين جمهورك المستهدف؟'): array
    {
        return $this->multi($label, self::COUNTRIES);
    }

    private function projectType(array $options = self::ONCE_OR_ONGOING): array
    {
        return $this->choice('هل العمل مرة واحدة أم مستمر؟', $options, true, ['filter' => 'project_type']);
    }

    private function adBudget(): array
    {
        return $this->count('ميزانية الإعلانات الشهرية المتوقعة', 'ج.م', 10000000, false,
            ['help' => 'المبلغ الذي يُدفع للمنصة نفسها، منفصل عن أجر المستقل.']);
    }

    /** On-site work: where it happens (feeds the «work_mode» and «city» filters). */
    private function onSite(bool $askMode = true): array
    {
        $city = ['type' => Q::TYPE_LOCATION, 'label' => 'المدينة أو المنطقة', 'placeholder' => 'مثال: القاهرة - مدينة نصر', 'required' => true, 'filter' => 'city'];

        return $askMode
            ? [$this->choice('مكان التنفيذ', ['حضوري في مكاني', 'في استوديو المستقل', 'عن بُعد'], true, ['key' => 'mode', 'filter' => 'work_mode']),
               $city + ['when' => ['mode', ['حضوري في مكاني', 'في استوديو المستقل']]]]
            : [$city];
    }

    /** The shared last section: its answers feed the freelancers' request filters. */
    private function shared(): array
    {
        return [
            ['type' => Q::TYPE_NUMBER, 'label' => 'ميزانيتك التقريبية لهذا العمل', 'required' => false, 'filter' => 'budget',
                'help' => 'المبلغ الذي تتوقع دفعه للمستقل. اتركه فارغًا إن لم تكن متأكدًا، وستصلك العروض.',
                'settings' => ['mode' => 'range', 'min' => 50, 'max' => 10000000, 'unit' => 'ج.م']],
            ['type' => Q::TYPE_DATE, 'label' => 'متى تحتاج استلام العمل؟', 'required' => false, 'filter' => 'deadline'],
            $this->choice('ما مدى استعجالك؟', ['عادي', 'خلال أسبوع', 'عاجل جدًا'], true, ['filter' => 'urgency']),
            $this->choice('مستوى الخبرة الذي تفضّله', ['مبتدئ بسعر اقتصادي', 'خبرة متوسطة', 'خبير محترف'], false, ['filter' => 'experience']),
        ];
    }

    // ── Questions per service ──────────────────────────────────────────

    /** A social platform expert (Facebook, Instagram, …): same shape, platform-specific needs and goals. */
    private function platformExpert(string $platform, array $needs, array $goals): array
    {
        return ["تفاصيل العمل على {$platform}", [
            $this->multi("ما المطلوب على {$platform}؟", $needs),
            $this->yesNo("هل لديك حساب {$platform} قائم؟", true, ['key' => 'has']),
            $this->url('رابط الحساب', false, ['when' => ['has', Q::YES]]),
            $this->choice('ما هدفك الأساسي؟', $goals),
            $this->field(),
            $this->countries(),
            $this->adBudget(),
            $this->projectType(),
        ]];
    }

    private function definitions(): array
    {
        $adNeeds = fn (string $extra) => ['إعداد أو تحسين الحساب', 'إعلانات ممولة', 'إدارة المحتوى والنشر', $extra, 'تقارير وتحليل الأداء'];
        $salesGoals = ['زيادة المبيعات', 'جذب عملاء محتملين (رسائل / أرقام)', 'زيادة المتابعين والتفاعل', 'التعريف بالعلامة التجارية'];
        $postsCount = ['أقل من 10', '10 - 20', '20 - 30', 'أكثر من 30'];

        return [
            // ── التسويق والإعلانات ──
            'التسويق' => ['تفاصيل احتياجك التسويقي', [
                $this->multi('ما الذي تحتاجه؟', ['خطة تسويقية', 'إدارة حملات إعلانية', 'إدارة السوشيال ميديا', 'كتابة وصناعة المحتوى', 'تحسين الظهور في جوجل (SEO)', 'لست متأكدًا، أحتاج استشارة']),
                $this->field(),
                $this->choice('في أي مرحلة نشاطك؟', ['فكرة جديدة لم تبدأ بعد', 'نشاط قائم ونريد النمو', 'نشاط قائم والمبيعات تراجعت']),
                $this->url('رابط موقعك أو أهم حساب لديك'),
                $this->long('من هم عملاؤك المستهدفون؟', 'العمر، المكان، الاهتمامات… بالتقريب'),
                $this->projectType(),
            ]],
            'خبير فيس بوك – إعداد وإعلان واحتراف الظهور' => $this->platformExpert('فيسبوك', $adNeeds('ربط واتساب وماسنجر بالإعلانات'), $salesGoals),
            'خبير إنستغرام – بروفايل جاذب وإعلان بصري مؤثر' => $this->platformExpert('إنستغرام', $adNeeds('تصميم بروفايل جذاب (هايلايت وبايو)'), $salesGoals),
            'خبير تويتر – تواجد مؤثر وإعلان موجه' => $this->platformExpert('تويتر (X)', $adNeeds('كتابة تغريدات وسلاسل (ثريد)'), ['زيادة المتابعين والتأثير', 'زيادة الزيارات للموقع', 'زيادة المبيعات', 'بناء العلامة الشخصية']),
            'خبير تيك توك – إعداد الملف الشخصي وإعلان ممول فعال' => $this->platformExpert('تيك توك', $adNeeds('أفكار وسكربتات فيديوهات'), $salesGoals),
            'خبير لينكد إن – بروفايل احترافي وإعلان مهني' => $this->platformExpert('لينكد إن', ['تحسين البروفايل الشخصي', 'إنشاء أو تحسين صفحة الشركة', 'إعلانات ممولة', 'كتابة منشورات مهنية', 'تقارير وتحليل الأداء'], ['الوصول لعملاء شركات (B2B)', 'التوظيف وجذب الكفاءات', 'بناء العلامة الشخصية', 'زيادة المتابعين للشركة']),
            'خبير سناب شات – إدارة احترافية وإعلان مخصص للمنطقة' => $this->platformExpert('سناب شات', $adNeeds('إعلانات موجهة لمنطقة جغرافية'), $salesGoals),
            'خبير يوتيوب – قناة احترافية وإعلانات مدروسة' => $this->platformExpert('يوتيوب', ['إنشاء أو تحسين القناة', 'إعلانات يوتيوب', 'تحسين ظهور الفيديوهات (SEO)', 'إدارة ونشر المحتوى', 'تقارير وتحليل الأداء'], ['زيادة المشتركين والمشاهدات', 'زيادة المبيعات', 'التعريف بالعلامة التجارية', 'تحقيق دخل من القناة']),
            'خبير إعلانات Google' => ['تفاصيل حملة جوجل', [
                $this->multi('أي نوع من إعلانات جوجل تحتاج؟', ['إعلانات البحث', 'الإعلانات المصوّرة (Display)', 'إعلانات يوتيوب', 'إعلانات التسوق', 'Performance Max', 'لست متأكدًا']),
                $this->yesNo('هل لديك حساب إعلانات جوجل قائم؟'),
                $this->url('رابط الموقع أو الصفحة التي ستوجه لها الإعلانات', true),
                $this->choice('ما هدفك الأساسي؟', ['مبيعات من الموقع', 'مكالمات أو رسائل', 'زيارات للمتجر/الفرع', 'تسجيلات (Leads)']),
                $this->countries('أين تريد ظهور الإعلانات؟'),
                $this->adBudget(),
                $this->projectType(['إعداد الحملة فقط', 'إعداد وإدارة شهرية']),
            ]],
            'إدارة الحملات الإعلانية' => ['تفاصيل الحملات', [
                $this->multi('على أي منصات تريد الحملات؟', ['فيسبوك وإنستغرام', 'جوجل', 'تيك توك', 'سناب شات', 'يوتيوب', 'لينكد إن', 'تويتر (X)']),
                $this->choice('ما هدفك الأساسي؟', $salesGoals),
                $this->field(),
                $this->yesNo('هل أطلقت حملات من قبل؟', true, ['key' => 'before']),
                $this->long('ما نتائج الحملات السابقة؟ وما الذي لم يعجبك فيها؟', '', false, ['when' => ['before', Q::YES]]),
                $this->countries(),
                $this->adBudget(),
                $this->projectType(),
            ]],
            'وضع الاستراتيجيات التسويقية' => ['تفاصيل الاستراتيجية', [
                $this->field(),
                $this->choice('في أي مرحلة نشاطك؟', ['فكرة لم تبدأ بعد', 'بدأنا من أقل من سنة', 'نشاط قائم ونريد التوسع']),
                $this->choice('لأي مدة تريد الخطة؟', ['3 أشهر', '6 أشهر', 'سنة']),
                $this->multi('ما قنوات التسويق التي تستخدمها حاليًا؟', ['السوشيال ميديا', 'إعلانات ممولة', 'موقع إلكتروني', 'تسويق بالبريد', 'مؤثرون', 'لا شيء بعد'], false),
                $this->long('ما أكبر تحدٍّ تواجهه في التسويق الآن؟', 'مثال: الإعلانات مكلفة والمبيعات قليلة', true),
                $this->choice('كيف تريد استلام الاستراتيجية؟', ['ملف مكتوب (PDF)', 'عرض تقديمي', 'ملف + جلسة شرح أونلاين']),
            ]],
            'إنشاء قمع المبيعات' => ['تفاصيل قمع المبيعات', [
                $this->choice('ماذا تبيع؟', ['منتج ملموس', 'خدمة', 'منتج رقمي أو كورس', 'اشتراك شهري']),
                $this->multi('من أين يأتيك العملاء حاليًا؟', ['إعلانات ممولة', 'السوشيال ميديا', 'جوجل', 'التوصيات', 'لا يوجد مصدر ثابت']),
                $this->multi('ما الأدوات المتوفرة لديك؟', ['موقع أو متجر', 'صفحة هبوط', 'قائمة بريدية', 'واتساب بيزنس', 'نظام CRM', 'لا شيء بعد'], false),
                $this->multi('ما الأجزاء المطلوبة في القمع؟', ['صفحة هبوط', 'رسائل متابعة بالبريد أو واتساب', 'إعلانات لجذب الزوار', 'ربط وأتمتة الأدوات', 'عرض أو هدية لجذب العملاء']),
                $this->long('أين تخسر العملاء الآن في رأيك؟', 'مثال: زيارات كثيرة لكن الطلبات قليلة'),
            ]],
            'تحسين محركات البحث (SEO)' => ['تفاصيل السيو', [
                $this->url('رابط موقعك', true),
                $this->choice('على أي منصة موقعك؟', self::SITE_PLATFORMS),
                $this->multi('ما المطلوب؟', ['تدقيق سيو شامل', 'سيو داخلي للصفحات', 'كتابة محتوى متوافق مع السيو', 'بناء روابط خارجية', 'سيو محلي (خرائط جوجل)']),
                $this->long('ما الكلمات التي تريد الظهور بها في جوجل؟', 'كلمة في كل سطر، مثال: تصميم مطابخ القاهرة'),
                $this->countries('في أي دولة تريد الظهور؟'),
                $this->yesNo('هل الموقع مربوط بـ Google Search Console؟', false),
                $this->projectType(),
            ]],
            'استراتيجيات السيو التقني للمحترفين' => ['تفاصيل السيو التقني', [
                $this->url('رابط موقعك', true),
                $this->choice('على أي منصة موقعك؟', self::SITE_PLATFORMS),
                $this->multi('ما المشاكل التي تريد حلها؟', ['سرعة الموقع ومؤشرات Core Web Vitals', 'الأرشفة والفهرسة', 'أخطاء Search Console', 'البيانات المنظمة (Schema)', 'موقع متعدد اللغات', 'نقل موقع أو تغيير دومين']),
                $this->choice('عدد صفحات الموقع تقريبًا', ['أقل من 50', '50 - 500', 'أكثر من 500']),
                $this->yesNo('هل تستطيع إعطاء صلاحية Search Console والاستضافة؟'),
                $this->long('اشرح المشكلة أو ما لاحظته', 'مثال: صفحات مهمة لا تظهر في جوجل'),
            ]],
            'تحسين المتاجر' => ['تفاصيل المتجر', [
                $this->url('رابط المتجر', true),
                $this->choice('على أي منصة المتجر؟', ['شوبيفاي', 'سلة', 'زد', 'ووكومرس (ووردبريس)', 'أخرى']),
                $this->multi('ما الذي تريد تحسينه؟', ['التصميم وتجربة الشراء', 'صفحات المنتجات', 'سرعة المتجر', 'بوابات الدفع والشحن', 'السيو', 'نسبة تحويل الزوار لمشترين']),
                $this->choice('عدد المنتجات تقريبًا', ['أقل من 50', '50 - 300', 'أكثر من 300']),
                $this->choice('عدد الطلبات الشهرية حاليًا', ['المتجر جديد', 'أقل من 50', '50 - 500', 'أكثر من 500'], false),
            ]],

            // ── السوشيال ميديا والمحتوى ──
            'تصميم بوستات سوشيال ميديا' => ['تفاصيل التصميمات', [
                $this->choice('كم تصميمًا تحتاج؟', $postsCount),
                $this->multi('لأي منصات؟', self::PLATFORMS),
                $this->choice('من يكتب نصوص التصميمات؟', ['النصوص جاهزة عندي', 'أريد المصمم يكتبها', 'جزء جاهز وجزء مطلوب']),
                $this->yesNo('هل لديك هوية بصرية (شعار وألوان)؟', true, ['key' => 'identity']),
                $this->files('ارفع الشعار وملف الهوية', 'الشعار وألوانك (اختياري).') + ['when' => ['identity', Q::YES]],
                $this->url('رابط حسابك أو تصميم يعجبك كمثال'),
                $this->projectType(['دفعة واحدة', 'باقة شهرية مستمرة']),
            ]],
            'تصميم منشورات احترافية للسوشيال ميديا' => ['تفاصيل التصميمات', [
                $this->multi('ما نوع التصميمات؟', ['بوستات صور', 'كاروسيل (عدة صور)', 'ستوري', 'تصميمات للإعلانات الممولة', 'غلاف الصفحة']),
                $this->choice('كم تصميمًا تحتاج؟', $postsCount),
                $this->multi('لأي منصات؟', self::PLATFORMS),
                $this->choice('الأسلوب المفضل', ['بسيط وعصري', 'فاخر', 'مرح وملوّن', 'رسمي للشركات', 'اترك الاختيار للمصمم']),
                $this->yesNo('هل لديك هوية بصرية (شعار وألوان)؟', true, ['key' => 'identity']),
                $this->files('ارفع الشعار وملف الهوية', 'الشعار وألوانك (اختياري).') + ['when' => ['identity', Q::YES]],
                $this->projectType(['دفعة واحدة', 'باقة شهرية مستمرة']),
            ]],
            'إدارة السوشال ميديا' => ['تفاصيل إدارة الحسابات', [
                $this->multi('ما المنصات المطلوب إدارتها؟', self::PLATFORMS),
                $this->long('روابط حساباتك', 'رابط في كل سطر', false),
                $this->multi('ما المهام المطلوبة؟', ['خطة المحتوى الشهرية', 'التصميم', 'كتابة الكابشن', 'النشر والجدولة', 'الرد على الرسائل والتعليقات', 'تقرير شهري بالأداء']),
                $this->choice('كم منشورًا في الأسبوع؟', ['2 - 3', '4 - 5', 'يوميًا']),
                $this->field(),
                $this->projectType(['شهر تجريبي', 'إدارة شهرية مستمرة']),
            ]],
            'كتابة المحتوى' => ['تفاصيل المحتوى', [
                $this->multi('ما نوع المحتوى؟', ['مقالات مدونة', 'منشورات سوشيال ميديا', 'نصوص صفحات الموقع', 'وصف منتجات', 'نصوص إعلانية', 'سيناريو فيديو', 'نشرات بريدية']),
                $this->multi('بأي لغة أو لهجة؟', ['العربية الفصحى', 'العامية المصرية', 'اللهجة الخليجية', 'الإنجليزية']),
                $this->text('الكمية المطلوبة', 'مثال: 8 مقالات × 1000 كلمة، أو 30 منشور', true),
                $this->choice('أسلوب الكتابة', ['رسمي', 'ودود وبسيط', 'تسويقي حماسي', 'اترك الاختيار للكاتب']),
                $this->yesNo('هل تريد المحتوى متوافقًا مع السيو؟', false),
                $this->field(),
                $this->projectType(),
            ]],
            'صناعة المحتوى' => ['تفاصيل المحتوى', [
                $this->multi('ما شكل المحتوى؟', ['فيديو قصير (ريلز / تيك توك)', 'فيديو يوتيوب', 'منشورات وصور', 'بودكاست', 'محتوى مع مؤثرين']),
                $this->multi('لأي منصات؟', self::PLATFORMS),
                $this->yesNo('هل ستظهر أنت أو فريقك في المحتوى؟'),
                $this->text('موضوع المحتوى أو مجالك', 'مثال: نصائح تغذية، عقارات، تقنية', true),
                $this->choice('كم قطعة محتوى في الشهر؟', ['أقل من 8', '8 - 15', '15 - 30', 'أكثر من 30']),
                $this->projectType(),
            ]],
            'إنتاج محتوى السوشيال ميديا' => ['تفاصيل الإنتاج', [
                $this->multi('ما المطلوب إنتاجه؟', ['تصوير', 'مونتاج', 'تصميم', 'كتابة السكربت والكابشن']),
                $this->multi('لأي منصات؟', self::PLATFORMS),
                $this->choice('كم قطعة محتوى في الشهر؟', ['أقل من 8', '8 - 15', '15 - 30', 'أكثر من 30']),
                $this->field(),
                ...$this->onSite(),
                $this->projectType(),
            ]],

            // ── التصميم والمواقع ──
            'برمجة وتصميم مواقع إلكترونية' => ['تفاصيل الموقع', [
                $this->choice('ما نوع الموقع؟', ['موقع تعريفي لشركة', 'متجر إلكتروني', 'منصة أو نظام مخصص', 'مدونة أو موقع محتوى', 'موقع حجوزات']),
                $this->choice('عدد الصفحات تقريبًا', ['حتى 5 صفحات', '6 - 15 صفحة', 'أكثر من 15', 'لا أعرف']),
                $this->multi('الخصائص المطلوبة', ['دفع إلكتروني', 'أكثر من لغة', 'لوحة تحكم لإدارة المحتوى', 'حجز مواعيد', 'تسجيل أعضاء', 'ربط شركات الشحن', 'ربط واتساب'], false),
                $this->choice('المنصة المفضلة', ['ووردبريس', 'شوبيفاي / سلة / زد', 'برمجة خاصة', 'لا أعرف، اقترحوا الأنسب']),
                $this->yesNo('هل لديك دومين واستضافة؟'),
                $this->choice('هل المحتوى (نصوص وصور) جاهز؟', ['جاهز', 'جزء منه', 'أحتاج من يكتبه']),
                $this->url('موقع يعجبك كمثال'),
                $this->yesNo('هل تحتاج صيانة ودعمًا بعد التسليم؟', false),
            ]],
            'تحسين سرعة المواقع' => ['تفاصيل الموقع', [
                $this->url('رابط موقعك', true),
                $this->choice('على أي منصة موقعك؟', self::SITE_PLATFORMS),
                $this->multi('ما المشكلة التي تلاحظها؟', ['بطء التحميل على الهاتف', 'بطء التحميل على الكمبيوتر', 'نتيجة PageSpeed منخفضة', 'بطء لوحة التحكم', 'مشاكل في الاستضافة']),
                $this->yesNo('هل فحصت موقعك بخدمة «اختبر موقعك مجانًا» على نفّذها؟', false),
                $this->yesNo('هل تستطيع إعطاء صلاحية لوحة التحكم والاستضافة؟'),
            ]],
            'تصميم الهوية البصرية' => ['تفاصيل الهوية', [
                $this->text('اسم العلامة التجارية', 'كما سيظهر في التصميم', true),
                $this->field(),
                $this->multi('ما المطلوب في الهوية؟', ['الشعار', 'الألوان والخطوط', 'كروت شخصية', 'الأوراق الرسمية', 'قوالب السوشيال ميديا', 'دليل استخدام الهوية']),
                $this->yesNo('هل لديك شعار حالي؟', true, ['key' => 'logo']),
                $this->choice('ماذا نفعل بالشعار الحالي؟', ['نحافظ عليه ونبني حوله', 'نطوّره', 'نصمم شعارًا جديدًا'], true, ['when' => ['logo', Q::YES]]),
                $this->multi('الطابع الذي تفضّله', ['عصري بسيط', 'فاخر', 'مرح', 'كلاسيكي', 'تقني'], false),
                $this->text('ألوان تفضّلها أو ترفضها', 'مثال: أفضّل الأزرق، لا أريد الأحمر'),
                $this->files('أمثلة تعجبك أو الشعار الحالي'),
            ]],
            'تصميم الشعارات' => ['تفاصيل الشعار', [
                $this->text('الاسم المكتوب في الشعار', 'بالعربي و/أو الإنجليزي', true),
                $this->text('شعار لفظي (سلوجن) إن وُجد'),
                $this->field(),
                $this->choice('نوع الشعار', ['نص فقط', 'رمز مع نص', 'رمز فقط', 'لا أعرف، اقترحوا']),
                $this->multi('الطابع الذي تفضّله', ['عصري بسيط', 'فاخر', 'مرح', 'كلاسيكي', 'تقني'], false),
                $this->text('ألوان تفضّلها أو ترفضها'),
                $this->files('شعارات تعجبك كأمثلة'),
            ]],
            'تصميم صفحات الهبوط' => ['تفاصيل صفحة الهبوط', [
                $this->choice('ما هدف الصفحة؟', ['جمع بيانات عملاء محتملين', 'بيع منتج أو خدمة', 'التسجيل في حدث أو كورس', 'تحميل تطبيق']),
                $this->long('ما الذي تقدّمه الصفحة؟', 'المنتج أو العرض باختصار', true),
                $this->choice('هل نصوص الصفحة جاهزة؟', ['جاهزة', 'جزء منها', 'أحتاج من يكتبها']),
                $this->choice('المنصة المفضلة', ['ووردبريس (إليمنتور)', 'شوبيفاي', 'برمجة خاصة', 'أي منصة مناسبة']),
                $this->multi('الربط المطلوب', ['بيكسل فيسبوك / تيك توك', 'جوجل أناليتكس', 'زر واتساب', 'نموذج بيانات يصل للبريد', 'بوابة دفع'], false),
                $this->url('صفحة تعجبك كمثال'),
            ]],

            // ── الإعلام والإنتاج ──
            'إنتاج الفيديو والمونتاج' => ['تفاصيل الفيديو', [
                $this->choice('ما نوع الفيديو؟', ['إعلان', 'فيديو تعريفي بالشركة', 'ريلز قصيرة', 'فيديو يوتيوب', 'موشن جرافيك', 'فيديو مناسبة أو حدث']),
                $this->choice('مدة الفيديو', ['أقل من دقيقة', '1 - 3 دقائق', '3 - 10 دقائق', 'أكثر من 10 دقائق']),
                $this->count('عدد الفيديوهات', 'فيديو', 500, true),
                $this->choice('هل المادة المصورة جاهزة؟', ['جاهزة، أحتاج المونتاج فقط', 'مطلوب التصوير أيضًا'], true, ['key' => 'footage']),
                $this->yesNo('هل تحتاج تعليقًا صوتيًا (فويس أوفر)؟', false),
                ...array_map(fn ($q) => $q + ['when' => ['footage', 'مطلوب التصوير أيضًا']], $this->onSite(false)),
            ]],
            'تقديم البرامج' => ['تفاصيل التقديم', [
                $this->choice('ما المطلوب تقديمه؟', ['برنامج يوتيوب', 'بودكاست', 'حدث أو مؤتمر', 'إعلان', 'برنامج تلفزيوني']),
                $this->multi('اللغة أو اللهجة', ['العربية الفصحى', 'العامية المصرية', 'اللهجة الخليجية', 'الإنجليزية']),
                $this->count('عدد الحلقات أو المرات', 'حلقة', 500),
                $this->choice('تفضيل المقدّم', ['لا يهم', 'مقدّم', 'مقدّمة'], false),
                ...$this->onSite(),
            ]],
            'إعلام' => ['تفاصيل الخدمة الإعلامية', [
                $this->multi('ما الذي تحتاجه؟', ['كتابة بيان صحفي', 'نشر خبر في مواقع إخبارية', 'ظهور إعلامي (لقاء أو مقابلة)', 'إدارة العلاقات الإعلامية', 'إدارة أزمة إعلامية']),
                $this->field(),
                $this->multi('الوسائل المستهدفة', ['مواقع إخبارية', 'صحف', 'قنوات تلفزيونية', 'إذاعة', 'منصات السوشيال'], false),
                $this->countries('في أي دولة؟'),
                $this->long('ما الخبر أو المناسبة؟', '', true),
            ]],
            'تصوير المنتجات' => ['تفاصيل التصوير', [
                $this->count('عدد المنتجات', 'منتج', 5000, true),
                $this->multi('نوع الصور', ['خلفية بيضاء للمتاجر', 'صور إبداعية (لايف ستايل)', 'فيديو قصير للمنتج']),
                $this->text('ما نوع المنتجات؟', 'مثال: ملابس، عطور، أطعمة، إلكترونيات', true),
                $this->yesNo('هل تحتاج تعديل الصور بعد التصوير؟', false),
                ...$this->onSite(),
            ]],
            'تصوير الفيديو' => ['تفاصيل التصوير', [
                $this->choice('ما المطلوب تصويره؟', ['إعلان', 'حدث أو مناسبة', 'مقابلات', 'فيديو تعريفي', 'عقارات أو أماكن']),
                $this->choice('مدة التصوير', ['بضع ساعات', 'يوم كامل', 'أكثر من يوم']),
                $this->multi('معدات خاصة', ['درون', 'إضاءة احترافية', 'صوت احترافي', 'أكثر من كاميرا'], false),
                $this->yesNo('هل تحتاج المونتاج أيضًا؟'),
                ...$this->onSite(false),
            ]],
            'تصوير الريلز' => ['تفاصيل الريلز', [
                $this->choice('كم ريلز تحتاج؟', ['أقل من 5', '5 - 10', '10 - 20', 'أكثر من 20']),
                $this->choice('من سيظهر في الفيديوهات؟', ['أنا أو فريقي', 'موديل أو ممثل من المستقل', 'المنتج فقط بدون أشخاص']),
                $this->choice('هل الأفكار والسكربت جاهزة؟', ['جاهزة', 'أحتاج أفكارًا وسكربت']),
                $this->yesNo('هل تحتاج المونتاج أيضًا؟'),
                $this->multi('لأي منصات؟', ['إنستغرام', 'تيك توك', 'فيسبوك', 'يوتيوب شورتس', 'سناب شات']),
                ...$this->onSite(false),
            ]],
            'المونتاج' => ['تفاصيل المونتاج', [
                $this->count('عدد الفيديوهات', 'فيديو', 500, true),
                $this->choice('مدة كل فيديو بعد المونتاج', ['أقل من دقيقة', '1 - 3 دقائق', '3 - 10 دقائق', 'أكثر من 10 دقائق']),
                $this->multi('الأسلوب المطلوب', ['إيقاع سريع (ريلز)', 'هادئ واحترافي', 'موشن جرافيك', 'ترجمة نصية على الشاشة', 'تصحيح ألوان']),
                $this->yesNo('هل تحتاج موسيقى أو تعليقًا صوتيًا؟', false),
                $this->url('فيديو يعجبك كمثال'),
            ]],
            'إنتاج بودكاست' => ['تفاصيل البودكاست', [
                $this->choice('مرحلة البودكاست', ['فكرة جديدة', 'قائم ونريد تحسينه']),
                $this->multi('ما المطلوب؟', ['الفكرة وتخطيط الحلقات', 'التسجيل في استوديو', 'مونتاج الصوت أو الفيديو', 'النشر على المنصات', 'تصميم الغلاف']),
                $this->choice('الشكل', ['صوت فقط', 'صوت وفيديو']),
                $this->count('عدد الحلقات', 'حلقة', 500),
                ...$this->onSite(),
            ]],
            'إنتاج البرامج التلفزيونية' => ['تفاصيل البرنامج', [
                $this->choice('نوع البرنامج', ['حواري', 'وثائقي قصير', 'ترفيهي', 'ثقافي أو ديني', 'رياضي']),
                $this->count('عدد الحلقات', 'حلقة', 500, true),
                $this->choice('مدة الحلقة', ['أقل من 15 دقيقة', '15 - 30 دقيقة', 'أكثر من 30 دقيقة']),
                $this->multi('ما المطلوب؟', ['الإعداد والكتابة', 'التصوير', 'الإخراج', 'المونتاج والجرافيك', 'التقديم']),
                $this->yesNo('هل هناك قناة أو منصة عرض متفق معها؟', false),
                ...$this->onSite(false),
            ]],
            'التغطيات الإعلامية' => ['تفاصيل التغطية', [
                $this->choice('نوع الحدث', ['مؤتمر', 'افتتاح', 'معرض', 'حفل', 'حدث رياضي', 'أخرى']),
                $this->multi('نوع التغطية', ['تصوير فوتوغرافي', 'تصوير فيديو', 'بث مباشر', 'تغطية لحظية على السوشيال', 'خبر صحفي']),
                $this->choice('مدة الحدث', ['بضع ساعات', 'يوم كامل', 'أكثر من يوم']),
                ['type' => Q::TYPE_DATE, 'label' => 'تاريخ الحدث', 'required' => true],
                ...$this->onSite(false),
            ]],
            'إنتاج الأفلام الوثائقية' => ['تفاصيل الفيلم', [
                $this->long('موضوع الفيلم', 'باختصار: عن ماذا يحكي الفيلم؟', true),
                $this->choice('المدة المتوقعة', ['أقل من 10 دقائق', '10 - 30 دقيقة', 'أكثر من 30 دقيقة']),
                $this->choice('في أي مرحلة المشروع؟', ['فكرة فقط', 'لدي بحث أو سيناريو', 'لدي مواد مصورة']),
                $this->multi('ما المطلوب؟', ['البحث والكتابة', 'التصوير', 'المقابلات', 'المونتاج والجرافيك', 'التعليق الصوتي']),
                $this->text('أماكن التصوير', 'مثال: القاهرة والإسكندرية'),
            ]],

            // ── التحليل ودراسة السوق ──
            'تحليل البيانات' => ['تفاصيل التحليل', [
                $this->multi('أين توجد البيانات؟', ['Excel', 'Google Sheets', 'قاعدة بيانات', 'Google Analytics', 'المتجر أو نظام CRM']),
                $this->long('ما السؤال الذي تريد أن تجيب عنه البيانات؟', 'مثال: أي المنتجات أكثر ربحًا ولماذا تراجعت المبيعات؟', true),
                $this->multi('كيف تريد النتيجة؟', ['تقرير مكتوب', 'داشبورد تفاعلية (Power BI / Looker Studio)', 'عرض تقديمي']),
                $this->choice('حجم البيانات تقريبًا', ['أقل من 1000 صف', '1000 - 100 ألف', 'أكثر من 100 ألف', 'لا أعرف'], false),
                $this->files('عينة من البيانات', 'بدون بيانات حساسة (اختياري).'),
                $this->projectType(['مرة واحدة', 'تحليل دوري مستمر']),
            ]],
            'تحليل المنافسين' => ['تفاصيل التحليل', [
                $this->field(),
                $this->long('من هم منافسوك؟', 'الأسماء أو الروابط، واحد في كل سطر', true),
                $this->multi('ما الجوانب المطلوب تحليلها؟', ['الأسعار والعروض', 'التسويق والسوشيال ميديا', 'المنتجات والخدمات', 'المواقع والسيو', 'آراء العملاء']),
                $this->countries('في أي سوق؟'),
                $this->choice('كيف تريد النتيجة؟', ['تقرير مكتوب', 'عرض تقديمي', 'تقرير + جلسة شرح']),
            ]],
            'دراسة السوق' => ['تفاصيل الدراسة', [
                $this->long('ما الفكرة أو المنتج؟', '', true),
                $this->countries('في أي سوق؟'),
                $this->multi('ما المطلوب في الدراسة؟', ['حجم السوق', 'شرائح العملاء', 'المنافسون', 'التسعير المناسب', 'دراسة جدوى مالية']),
                $this->choice('طريقة جمع المعلومات', ['بحث مكتبي', 'استبيانات ومقابلات', 'الاثنان معًا']),
                $this->choice('لماذا تحتاج الدراسة؟', ['لعرضها على مستثمرين أو جهة تمويل', 'لاتخاذ قرار شخصي', 'لخطة توسّع']),
            ]],
            'إعداد التقارير' => ['تفاصيل التقرير', [
                $this->choice('نوع التقرير', ['أداء تسويقي', 'مالي', 'تقرير سنوي', 'تقرير مشروع', 'أخرى']),
                $this->yesNo('هل البيانات أو المعلومات متوفرة لديك؟'),
                $this->multi('بأي لغة؟', ['العربية', 'الإنجليزية']),
                $this->yesNo('هل تحتاج تصميمًا احترافيًا للتقرير؟', false),
                $this->projectType(['مرة واحدة', 'شهري', 'ربع سنوي']),
            ]],

            // ── تطوير الذات ──
            'تطوير الذات' => ['تفاصيل التدريب', [
                $this->multi('في أي مجال تريد التطور؟', ['مهارات التواصل والإلقاء', 'إدارة الوقت والإنتاجية', 'القيادة', 'التطوير المهني والوظيفي', 'الثقة بالنفس']),
                $this->choice('شكل التدريب', ['جلسات فردية أونلاين', 'جلسات فردية حضورية', 'ورشة لمجموعة'], true, ['key' => 'format']),
                $this->count('عدد الجلسات المتوقع', 'جلسة', 100),
                $this->long('ما الذي تريد الوصول إليه؟', 'مثال: أتكلم بثقة في الاجتماعات', true),
                ['type' => Q::TYPE_LOCATION, 'label' => 'المدينة أو المنطقة', 'required' => true, 'filter' => 'city',
                    'when' => ['format', ['جلسات فردية حضورية', 'ورشة لمجموعة']]],
            ]],
        ];
    }
}
