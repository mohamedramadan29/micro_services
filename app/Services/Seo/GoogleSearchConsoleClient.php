<?php

namespace App\Services\Seo;

use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use RuntimeException;

/**
 * Free Search Console access through a Google Cloud service account: its JSON
 * key file is on the server (GSC_CREDENTIALS = path) and its email is added as
 * a user of the property in Search Console (GSC_SITE_URL, e.g.
 * "sc-domain:nafizha.com" or "https://nafizha.com/").
 */
class GoogleSearchConsoleClient implements SearchConsoleClient
{
    public function configured(): bool
    {
        $path = (string) config('services.search_console.credentials');

        return $path !== '' && is_file($path) && filled(config('services.search_console.site_url'));
    }

    public function queries(string $start, string $end, int $limit): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Search Console غير مربوط: أضف GSC_CREDENTIALS و GSC_SITE_URL');
        }

        $client = new Client();
        $client->setAuthConfig(config('services.search_console.credentials'));
        $client->addScope(SearchConsole::WEBMASTERS_READONLY);
        $service = new SearchConsole($client);

        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate($start);
        $request->setEndDate($end);
        $request->setDimensions(['query']);
        $request->setRowLimit(min($limit, 25000));

        $response = $service->searchanalytics->query(config('services.search_console.site_url'), $request);

        return array_map(fn ($row) => [
            'query' => (string) ($row->getKeys()[0] ?? ''),
            'clicks' => (int) $row->getClicks(),
            'impressions' => (int) $row->getImpressions(),
            'ctr' => (float) $row->getCtr(),
            'position' => (float) $row->getPosition(),
        ], $response->getRows() ?? []);
    }
}
