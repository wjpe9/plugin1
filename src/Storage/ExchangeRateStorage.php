<?php
declare(strict_types=1);

namespace CuPePricesSystem\Storage;

use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class ExchangeRateStorage
{
    /**
     * @return array<string,mixed>|null
     */
    public function get(): ?array
    {
        $data = get_option(Defaults::exchangeRatesOptionName(), null);

        if (!is_array($data)) {
            return null;
        }

        return $this->sanitizePayload($data);
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function save(array $payload): bool
    {
        $payload = $this->sanitizePayload($payload);

        return update_option(Defaults::exchangeRatesOptionName(), $payload, false);
    }

    public function isExpired(?array $payload): bool
    {
        if (!is_array($payload)) {
            return true;
        }

        $expiresAt = isset($payload['expires_at_gmt']) ? (string) $payload['expires_at_gmt'] : '';

        if ($expiresAt === '') {
            return true;
        }

        $expiresTs = strtotime($expiresAt . ' GMT');

        if ($expiresTs === false) {
            return true;
        }

        return time() >= $expiresTs;
    }

    public function hasUsableRates(?array $payload): bool
    {
        if (!is_array($payload)) {
            return false;
        }

        if (!isset($payload['rates']) || !is_array($payload['rates']) || empty($payload['rates'])) {
            return false;
        }

        return isset($payload['rates']['USD']) && is_numeric($payload['rates']['USD']);
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        $rates = [];
        $rawRates = isset($payload['rates']) && is_array($payload['rates']) ? $payload['rates'] : [];

        foreach ($rawRates as $currency => $rate) {
            $currency = strtoupper((string) $currency);

            if (!in_array($currency, Defaults::supportedCurrencies(), true)) {
                continue;
            }

            if (!is_numeric($rate)) {
                continue;
            }

            $rates[$currency] = (float) $rate;
        }

        if (!isset($rates['USD'])) {
            $rates['USD'] = 1.0;
        }

        return [
            'base'            => isset($payload['base']) ? strtoupper((string) $payload['base']) : Defaults::baseCurrency(),
            'provider'        => isset($payload['provider']) ? (string) $payload['provider'] : Defaults::exchangeRatesProvider(),
            'status'          => isset($payload['status']) ? (string) $payload['status'] : 'unknown',
            'fetched_at_gmt'  => isset($payload['fetched_at_gmt']) ? (string) $payload['fetched_at_gmt'] : '',
            'expires_at_gmt'  => isset($payload['expires_at_gmt']) ? (string) $payload['expires_at_gmt'] : '',
            'rates'           => $rates,
            'last_error'      => isset($payload['last_error']) ? (string) $payload['last_error'] : '',
        ];
    }
}