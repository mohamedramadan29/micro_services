<?php

namespace Tests\Fakes;

use App\Services\SiteTests\SiteTestEngine;
use RuntimeException;

/** Returns canned results; `fail` makes every device fail, `failDevices` only some. */
class FakeSiteTestEngine implements SiteTestEngine
{
    public bool $fail = false;

    public array $failDevices = [];

    public array $calls = [];

    public function name(): string
    {
        return 'Fake engine';
    }

    public function run(string $url, string $device, array $categories): array
    {
        $this->calls[] = [$url, $device, $categories];
        if ($this->fail || in_array($device, $this->failDevices, true)) {
            throw new RuntimeException('engine down');
        }

        $scores = array_fill_keys($categories, 70);
        if (in_array('mobile', $categories, true) && $device === 'desktop') {
            $scores['mobile'] = null;
        }

        return [
            'scores' => $scores,
            'issues' => [
                ['code' => 'meta-description', 'category' => 'seo', 'title' => 'لا يحتوي المستند على وصف تعريفي', 'description' => 'الوصف يساعد محركات البحث', 'evidence' => null, 'score' => 0.0, 'weight' => 1.0],
                ['code' => 'unused-javascript', 'category' => 'performance', 'title' => 'تقليل JavaScript غير المستخدَمة', 'description' => null, 'evidence' => 'Potential savings of 120 KiB', 'score' => 0.6, 'weight' => 0.0],
                ['code' => 'errors-in-console', 'category' => 'best-practices', 'title' => 'تم تسجيل أخطاء المتصفح', 'description' => null, 'evidence' => null, 'score' => 0.0, 'weight' => 1.0],
            ],
            'raw' => json_encode(['device' => $device]),
        ];
    }
}
