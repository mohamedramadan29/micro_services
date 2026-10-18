<?php

namespace App\Services\SiteTests;

/** A website checker (PageSpeed Insights in production, a fake in tests). */
interface SiteTestEngine
{
    public function name(): string;

    /**
     * Check one URL for one device ("mobile" or "desktop").
     *
     * @param  string[]  $categories  keys of SiteTestSettings::CATEGORIES
     * @return array{scores: array<string, ?int>, issues: array<int, array{code: string, category: string, title: string, description: ?string, evidence: ?string, score: float, weight: float}>, raw: ?string}
     *
     * @throws \RuntimeException when the check could not be done
     */
    public function run(string $url, string $device, array $categories): array;
}
