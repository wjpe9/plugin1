<?php
declare(strict_types=1);

namespace CuPePricesSystem\Support;

if (!defined('ABSPATH')) {
    exit;
}

final class CountryMap
{
    /**
     * @return array<string,string>
     */
    public static function currencyByCountry(): array
    {
        return [
            'PE' => 'PEN',
            'ES' => 'EUR',
            'BR' => 'BRL',
            'CL' => 'CLP',
            'US' => 'USD',

            // Extras útiles
            'AR' => 'USD',
            'MX' => 'USD',
            'CO' => 'USD',
            'EC' => 'USD',
            'UY' => 'USD',
            'PY' => 'USD',
            'BO' => 'USD',
            'GB' => 'USD',
            'FR' => 'EUR',
            'DE' => 'EUR',
            'IT' => 'EUR',
            'NL' => 'EUR',
            'PT' => 'EUR',
        ];
    }

    public static function currencyForCountry(string $countryCode): string
    {
        $countryCode = strtoupper(trim($countryCode));
        $map = self::currencyByCountry();

        if (isset($map[$countryCode])) {
            return $map[$countryCode];
        }

        return Defaults::baseCurrency();
    }
}