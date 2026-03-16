<?php
declare(strict_types=1);

namespace CuPePricesSystem\Support;

if (!defined('ABSPATH')) {
    exit;
}

final class Defaults
{
    public static function baseCurrency(): string
    {
        return 'USD';
    }

    public static function defaultAcfField(): string
    {
        return 'precio';
    }

    public static function exchangeRatesOptionName(): string
    {
        return 'cupe_prices_exchange_rates';
    }

    public static function exchangeRatesRefreshInterval(): int
    {
        return 2 * DAY_IN_SECONDS; // 48 horas
    }

    /**
     * @return string[]
     */
    public static function supportedCurrencies(): array
    {
        return ['USD', 'EUR', 'PEN', 'BRL', 'CLP'];
    }

    public static function exchangeRatesProvider(): string
    {
        return 'frankfurter';
    }

    public static function exchangeRatesApiBaseUrl(): string
    {
        return 'https://api.frankfurter.app/latest';
    }

    public static function exchangeRatesRequestTimeout(): int
    {
        return 15;
    }

    public static function currencyCookieName(): string
    {
        return 'cupe_currency';
    }

    public static function adminPageSlug(): string
    {
        return 'cupe-prices-system';
    }

    public static function adminCapability(): string
    {
        return 'manage_options';
    }

    public static function defaultCountryCode(): string
    {
        return 'US';
    }
}