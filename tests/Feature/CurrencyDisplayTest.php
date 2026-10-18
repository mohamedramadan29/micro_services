<?php

namespace Tests\Feature;

use App\Models\admin\Package;
use App\Services\Currency\ExchangeRates;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Approximate USD prices site-wide (user decision 2026-10-15): display only, payments stay EGP. */
class CurrencyDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function rate(float $rate = 0.02, ?int $at = null): void
    {
        Cache::forever(ExchangeRates::CACHE_KEY, ['rate' => $rate, 'at' => $at ?? now()->getTimestamp()]);
    }

    public function test_the_daily_rate_is_fetched_and_bad_answers_keep_the_last_good_one(): void
    {
        Http::fakeSequence('open.er-api.com/*')
            ->push(['result' => 'success', 'rates' => ['USD' => 0.0205]])
            ->push(['result' => 'error'], 500)
            ->push(['result' => 'success', 'rates' => ['USD' => 49]]); // EGP per USD by mistake: refused

        $this->artisan('fx:refresh')->expectsOutputToContain('1 EGP = 0.0205 USD')->assertSuccessful();
        $this->artisan('fx:refresh')->expectsOutputToContain('last good rate is kept');
        $this->artisan('fx:refresh');

        $this->assertSame(0.0205, app(ExchangeRates::class)->usdPerEgp());
    }

    public function test_prices_show_an_approximate_dollar_amount_only_when_chosen(): void
    {
        $this->rate(0.02);
        Package::create(['name' => 'الباقة الذهبية', 'title' => 'ذهبي', 'slug' => 'gold', 'description' => 'd', 'price' => 5000]);

        $this->get(route('packages.index'))->assertOk()->assertSee('5,000 ج.م')->assertDontSee('≈ $');
        $this->withCookie(Money::COOKIE, 'USD')->get(route('packages.index'))->assertSee('5,000 ج.م (≈ $100)');
    }

    public function test_the_switch_remembers_the_choice_and_hides_without_a_fresh_rate(): void
    {
        $this->get(route('currency.switch', 'USD'))->assertRedirect()->assertCookie(Money::COOKIE, 'USD');
        $this->get('currency/GBP')->assertNotFound();

        $this->get('/')->assertDontSee('rf-fx-switch');
        $this->rate();
        $this->get('/')->assertSee('rf-fx-switch');

        // A rate older than three days is not shown as if it were current.
        $this->rate(0.02, now()->subDays(4)->getTimestamp());
        $this->get('/')->assertDontSee('rf-fx-switch');
        $this->withCookie(Money::COOKIE, 'USD')->get(route('currency.switch', 'USD'));
        $this->assertSame('450 ج.م', Money::show(450));
    }

    public function test_stored_and_sent_amounts_never_carry_dollars(): void
    {
        $this->rate(0.02);
        $this->withCookie(Money::COOKIE, 'USD')->get('/');

        // format() is what notifications, emails and payment confirmations use.
        $this->assertSame('1,250.50 ج.م', Money::format(1250.5));
    }
}
