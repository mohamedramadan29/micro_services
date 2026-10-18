<?php

namespace App\Support;

use App\Services\Currency\ExchangeRates;

/**
 * Amounts in the payment currency (EGP). `format()` is the plain amount, for
 * anything stored or sent (notifications, emails, confirmations); `show()` is
 * for pages and adds an approximate dollar amount when the visitor chose USD.
 */
class Money
{
    public const COOKIE = 'currency';

    public static function label(): string
    {
        return (string) config('services.easykash.currency_label', 'ج.م');
    }

    /** "450 ج.م" — no decimals unless needed. */
    public static function format($amount): string
    {
        $amount = (float) $amount;
        $number = floor($amount) == $amount ? number_format($amount) : number_format($amount, 2);

        return $number . ' ' . self::label();
    }

    /** "450 ج.م (≈ $9.20)" when the visitor shows prices in USD and a fresh rate exists. */
    public static function show($amount): string
    {
        $usd = self::usd($amount);

        return self::format($amount) . ($usd !== null ? ' (≈ $' . $usd . ')' : '');
    }

    public static function usd($amount): ?string
    {
        if (! self::wantsUsd() || ! ($rate = app(ExchangeRates::class)->usdPerEgp())) {
            return null;
        }
        $value = (float) $amount * $rate;

        return $value >= 100 ? number_format($value) : number_format($value, 2);
    }

    /** The visitor's choice (cookie); jobs and commands have none, so they always get EGP. */
    public static function wantsUsd(): bool
    {
        return request()->cookie(self::COOKIE) === 'USD';
    }

    /** The switch is offered only while a fresh rate exists. */
    public static function usdAvailable(): bool
    {
        return app(ExchangeRates::class)->usdPerEgp() !== null;
    }
}
