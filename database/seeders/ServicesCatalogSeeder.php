<?php

namespace Database\Seeders;

use App\Http\Traits\Slug_Trait;
use App\Models\admin\Category;
use App\Models\admin\SubCategory;
use Illuminate\Database\Seeder;

/**
 * The 41 services in 6 groups from the client brief «متطلبات تطوير قسم
 * الخدمات» (page 7), plus «تحسين سرعة المواقع» (client, 2026-10-16). Safe to run again: existing names are kept as they are
 * (the admin may have edited them), only missing ones are added.
 *
 *   php artisan db:seed --class=ServicesCatalogSeeder
 */
class ServicesCatalogSeeder extends Seeder
{
    use Slug_Trait;

    public const GROUPS = [
        ['التسويق والإعلانات', 'حملات، منصات، واستراتيجيات نمو', 'bi-megaphone', [
            'التسويق',
            'خبير فيس بوك – إعداد وإعلان واحتراف الظهور',
            'خبير إنستغرام – بروفايل جاذب وإعلان بصري مؤثر',
            'خبير تويتر – تواجد مؤثر وإعلان موجه',
            'خبير تيك توك – إعداد الملف الشخصي وإعلان ممول فعال',
            'خبير لينكد إن – بروفايل احترافي وإعلان مهني',
            'خبير سناب شات – إدارة احترافية وإعلان مخصص للمنطقة',
            'خبير يوتيوب – قناة احترافية وإعلانات مدروسة',
            'خبير إعلانات Google',
            'إدارة الحملات الإعلانية',
            'وضع الاستراتيجيات التسويقية',
            'إنشاء قمع المبيعات',
            'تحسين محركات البحث (SEO)',
            'استراتيجيات السيو التقني للمحترفين',
            'تحسين المتاجر',
        ]],
        ['السوشيال ميديا والمحتوى', 'تصميم وإدارة وكتابة محتوى الحسابات', 'bi-chat-heart', [
            'تصميم بوستات سوشيال ميديا',
            'تصميم منشورات احترافية للسوشيال ميديا',
            'إدارة السوشال ميديا',
            'كتابة المحتوى',
            'صناعة المحتوى',
            'إنتاج محتوى السوشيال ميديا',
        ]],
        ['التصميم والمواقع', 'مواقع، هويات، شعارات، وصفحات هبوط', 'bi-palette', [
            'برمجة وتصميم مواقع إلكترونية',
            // Added 2026-10-16 (client): the free site check's speed results lead here.
            'تحسين سرعة المواقع',
            'تصميم الهوية البصرية',
            'تصميم الشعارات',
            'تصميم صفحات الهبوط',
        ]],
        ['الإعلام والإنتاج', 'تصوير، فيديو، برامج، وتغطيات', 'bi-camera-video', [
            'إنتاج الفيديو والمونتاج',
            'تقديم البرامج',
            'إعلام',
            'تصوير المنتجات',
            'تصوير الفيديو',
            'تصوير الريلز',
            'المونتاج',
            'إنتاج بودكاست',
            'إنتاج البرامج التلفزيونية',
            'التغطيات الإعلامية',
            'إنتاج الأفلام الوثائقية',
        ]],
        ['التحليل ودراسة السوق', 'بيانات، منافسون، وتقارير', 'bi-graph-up-arrow', [
            'تحليل البيانات',
            'تحليل المنافسين',
            'دراسة السوق',
            'إعداد التقارير',
        ]],
        ['تطوير الذات', 'خدمات تطوير ذاتي', 'bi-person-arms-up', [
            'تطوير الذات',
        ]],
    ];

    public function run(): void
    {
        foreach (self::GROUPS as $groupIndex => [$name, $description, $icon, $services]) {
            $group = Category::firstOrCreate(['name' => $name], [
                'slug' => $this->CustomeSlug($name),
                'description' => $description,
                'icon' => $icon,
                'sort_order' => $groupIndex + 1,
                'status' => 1,
            ]);

            foreach ($services as $serviceIndex => $service) {
                SubCategory::firstOrCreate(['name' => $service, 'parent_id' => $group->id], [
                    'slug' => $this->CustomeSlug($service),
                    'sort_order' => $serviceIndex + 1,
                    'status' => 1,
                ]);
            }
        }
    }
}
