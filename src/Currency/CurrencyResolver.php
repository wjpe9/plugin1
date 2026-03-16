<?php
declare(strict_types=1);

namespace CuPePricesSystem\Currency;

use CuPePricesSystem\Geo\GeoCountryResolver;
use CuPePricesSystem\Support\CountryMap;
use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class CurrencyResolver
{
    private GeoCountryResolver $geoCountryResolver;

    public function __construct()
    {
        $this->geoCountryResolver = new GeoCountryResolver();
    }

    /**
     * @param array<string,mixed> $atts
     */
    public function resolve(array $atts): string
    {
        if (!empty($atts['currency'])) {
            return $this->sanitizeCurrency((string) $atts['currency']);
        }

        if (isset($_GET['cupe_currency'])) {
            $currency = $this->sanitizeCurrency((string) $_GET['cupe_currency']);
            $this->persistCurrency($currency);
            return $currency;
        }

        $cookieName = Defaults::currencyCookieName();

        if (!empty($_COOKIE[$cookieName])) {
            return $this->sanitizeCurrency((string) $_COOKIE[$cookieName]);
        }

        $countryCode = $this->geoCountryResolver->resolveCountryCode();
        $geoCurrency = CountryMap::currencyForCountry($countryCode);

        return $this->sanitizeCurrency($geoCurrency);
    }

    public function getResolvedCountryCode(): string
    {
        return $this->geoCountryResolver->resolveCountryCode();
    }

    private function sanitizeCurrency(string $currency): string
    {
        $currency = strtoupper(trim($currency));

        return in_array($currency, Defaults::supportedCurrencies(), true)
            ? $currency
            : Defaults::baseCurrency();
    }

    private function persistCurrency(string $currency): void
    {
        if (headers_sent()) {
            return;
        }

        setcookie(
            Defaults::currencyCookieName(),
            $currency,
            time() + MONTH_IN_SECONDS,
            COOKIEPATH ?: '/',
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );

        $_COOKIE[Defaults::currencyCookieName()] = $currency;
    }
}