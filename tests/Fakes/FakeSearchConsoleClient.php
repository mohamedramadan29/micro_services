<?php

namespace Tests\Fakes;

use App\Services\Seo\SearchConsoleClient;

class FakeSearchConsoleClient implements SearchConsoleClient
{
    public bool $connected = true;

    public array $rows = [];

    public array $calls = [];

    public function configured(): bool
    {
        return $this->connected;
    }

    public function queries(string $start, string $end, int $limit): array
    {
        $this->calls[] = [$start, $end, $limit];

        return $this->rows;
    }
}
