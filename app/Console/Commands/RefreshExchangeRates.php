<?php

namespace App\Console\Commands;

use App\Services\Currency\ExchangeRates;
use Illuminate\Console\Command;

class RefreshExchangeRates extends Command
{
    protected $signature = 'fx:refresh';

    protected $description = 'Fetch the EGP → USD rate used for approximate dollar prices';

    public function handle(ExchangeRates $rates): int
    {
        $rate = $rates->refresh();
        $this->info($rate ? '1 EGP = ' . $rate . ' USD' : 'Rate not updated (feed unavailable); the last good rate is kept.');

        return self::SUCCESS;
    }
}
