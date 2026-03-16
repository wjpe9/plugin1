<?php
declare(strict_types=1);

namespace CuPePricesSystem\Geo;

use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class GeoCountryResolver
{
    public function resolveCountryCode(): string
    {
        $candidates = [
            $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '',
            $_SERVER['CF_IPCOUNTRY'] ?? '',
            $_SERVER['HTTP_X_COUNTRY_CODE'] ?? '',
            $_SERVER['GEOIP_COUNTRY_CODE'] ?? '',
        ];

        foreach ($candidates as $candidate) {
            $country = $this->sanitizeCountryCode((string) $candidate);

            if ($country !== '') {
                return $country;
            }
        }

        return Defaults::defaultCountryCode();
    }

    private function sanitizeCountryCode(string $country): string
    {
        $country = strtoupper(trim($country));

        if ($country === '' || strlen($country) !== 2) {
            return '';
        }

        if (in_array($country, ['XX', 'T1'], true)) {
            return '';
        }

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : '';
    }
}