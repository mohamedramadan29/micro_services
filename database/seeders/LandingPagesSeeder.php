<?php

namespace Database\Seeders;

use App\Models\LandingPage;
use App\Services\Landing\LandingBuilder;
use Illuminate\Database\Seeder;

/** The starter landing page at /lp/start (client reference image), published. Idempotent by slug. */
class LandingPagesSeeder extends Seeder
{
    public function run(LandingBuilder $builder): void
    {
        if (LandingPage::where('slug', 'start')->exists()) {
            return;
        }
        $page = $builder->create([
            'title' => 'مشروعك له طريق واضح من الفكرة للتنفيذ',
            'slug' => 'start',
            'meta_description' => 'اطلب عرضًا لمشروعك، أو شخّص موقعك مجانًا، وابدأ مع مستقلين موثوقين باتفاق واضح يحفظ حق الطرفين.',
        ], true);
        $builder->publish($page);
    }
}
