<?php

namespace Database\Seeders;

use App\Models\admin\SubCategory;
use App\Models\SiteTestRule;
use Illuminate\Database\Seeder;

/**
 * Starting rules for the free site check: common PageSpeed audit codes →
 * the catalog service that fixes them (run after ServicesCatalogSeeder).
 * The admin edits them in «إعداد الفحص». Existing rules are left as they are.
 *
 *   php artisan db:seed --class=SiteTestRulesSeeder
 */
class SiteTestRulesSeeder extends Seeder
{
    /** service name => [priority => audit codes] */
    public const RULES = [
        'تحسين محركات البحث (SEO)' => [
            'high' => ['meta-description', 'document-title', 'is-crawlable', 'http-status-code'],
            'medium' => ['link-text', 'crawlable-anchors', 'hreflang', 'canonical', 'robots-txt', 'image-alt'],
        ],
        'تحسين سرعة المواقع' => [
            'high' => ['largest-contentful-paint', 'total-blocking-time'],
            'medium' => [
                'unused-javascript', 'unused-css-rules', 'render-blocking-resources', 'unminified-javascript', 'unminified-css',
                'uses-text-compression', 'uses-optimized-images', 'modern-image-formats', 'uses-responsive-images', 'offscreen-images',
                'first-contentful-paint', 'speed-index', 'cumulative-layout-shift', 'server-response-time',
            ],
        ],
        'برمجة وتصميم مواقع إلكترونية' => [
            'high' => ['errors-in-console', 'viewport', 'is-on-https'],
            'medium' => ['font-size', 'tap-targets', 'content-width', 'deprecations', 'color-contrast', 'button-name', 'label'],
        ],
    ];

    /** Speed rules first seeded on «برمجة وتصميم مواقع إلكترونية» move to the speed service unless the admin changed them. */
    private const MOVED_FROM = ['تحسين سرعة المواقع' => 'برمجة وتصميم مواقع إلكترونية'];

    public function run(): void
    {
        foreach (self::RULES as $serviceName => $priorities) {
            $service = SubCategory::where('name', $serviceName)->first();
            if (! $service) {
                $this->command?->warn('Service not found (run ServicesCatalogSeeder first): ' . $serviceName);
                continue;
            }
            $oldServiceId = isset(self::MOVED_FROM[$serviceName]) ? SubCategory::where('name', self::MOVED_FROM[$serviceName])->value('id') : null;
            foreach ($priorities as $priority => $codes) {
                foreach ($codes as $code) {
                    $rule = SiteTestRule::firstOrCreate(['audit_code' => $code], ['sub_category_id' => $service->id, 'priority' => $priority, 'active' => true]);
                    if ($oldServiceId && (int) $rule->sub_category_id === (int) $oldServiceId) {
                        $rule->update(['sub_category_id' => $service->id]);
                    }
                }
            }
        }
    }
}
