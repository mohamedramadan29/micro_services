<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\front\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'client_name' => 'أحمد محمد',
                'client_position' => 'مدير مشروع',
                'client_company' => 'شركة التقنية المتقدمة',
                'content' => 'خدمة ممتازة وفريق احترافي! ساعدونا في إنجاز مشروعنا في الوقت المحدد وبجودة عالية. أنصح بالتعامل معهم بشدة.',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'is_approved' => true,
                'sort_order' => 1,
            ],
            [
                'client_name' => 'سارة أحمد',
                'client_position' => 'مصممة جرافيك',
                'client_company' => 'استوديو الإبداع',
                'content' => 'منصة رائعة تربط بين أصحاب المشاريع والمستقلين. وجدت العديد من المشاريع المميزة من خلال المنصة.',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'is_approved' => true,
                'sort_order' => 2,
            ],
            [
                'client_name' => 'خالد العمر',
                'client_position' => 'رائد أعمال',
                'client_company' => 'شركة النمو الرقمي',
                'content' => 'تجربة ممتازة في التعامل مع المستقلين على المنصة. الجودة عالية والأسعار مناسبة جداً.',
                'rating' => 4,
                'is_featured' => false,
                'is_active' => true,
                'is_approved' => true,
                'sort_order' => 3,
            ],
            [
                'client_name' => 'منى سالم',
                'client_position' => 'مديرة التسويق',
                'client_company' => 'وكالة التسويق الحديث',
                'content' => 'ساعدتنا المنصة في العثور على أفضل المواهب لمشاريعنا. الدعم الفني ممتاز والاستجابة سريعة.',
                'rating' => 5,
                'is_featured' => true,
                'is_active' => true,
                'is_approved' => true,
                'sort_order' => 4,
            ],
            [
                'client_name' => 'عبدالله الرشيد',
                'client_position' => 'مطور ويب',
                'client_company' => 'مؤسسة التقنية',
                'content' => 'منصة موثوقة وآمنة للتعامل. تمكنت من الحصول على العديد من المشاريع المربحة من خلالها.',
                'rating' => 4,
                'is_featured' => false,
                'is_active' => true,
                'is_approved' => true,
                'sort_order' => 5,
            ],
            [
                'client_name' => 'فاطمة علي',
                'client_position' => 'مستشارة أعمال',
                'client_company' => 'مركز الاستشارات المتقدم',
                'content' => 'خدمة عملاء ممتازة ومنصة سهلة الاستخدام. أوصي بها لكل من يبحث عن جودة واحترافية.',
                'rating' => 5,
                'is_featured' => false,
                'is_active' => true,
                'is_approved' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Review::create($testimonial);
        }
    }
}
