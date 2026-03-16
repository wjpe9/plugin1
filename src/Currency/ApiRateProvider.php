<?php
//src\Currency\ApiRatesProvider.php

namespace CuPePricesSystem\Currency;

use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class ApiRateProvider
{
    /**
     * Consulta la API y devuelve un payload normalizado o null si falla.
     *
     * @return array<string,mixed>|null
     */
    public function fetchRates(): ?array
    {
        $base = Defaults::baseCurrency();
        $symbols = implode(',', Defaults::supportedCurrencies());

        $url = add_query_arg([
            'from' => $base,
            'to'   => $symbols,
        ], Defaults::exchangeRatesApiBaseUrl());

        $response = wp_remote_get($url, [
            'timeout' => Defaults::exchangeRatesRequestTimeout(),
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return [
                'base'            => $base,
                'provider'        => Defaults::exchangeRatesProvider(),
                'status'          => 'error',
                'fetched_at_gmt'  => gmdate('Y-m-d H:i:s'),
                'expires_at_gmt'  => gmdate('Y-m-d H:i:s', time() + Defaults::exchangeRatesRefreshInterval()),
                'rates'           => [],
                'last_error'      => $response->get_error_message(),
            ];
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($statusCode < 200 || $statusCode >= 300 || $body === '') {
            return [
                'base'            => $base,
                'provider'        => Defaults::exchangeRatesProvider(),
                'status'          => 'error',
                'fetched_at_gmt'  => gmdate('Y-m-d H:i:s'),
                'expires_at_gmt'  => gmdate('Y-m-d H:i:s', time() + Defaults::exchangeRatesRefreshInterval()),
                'rates'           => [],
                'last_error'      => 'HTTP error or empty response body',
            ];
        }

        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            return [
                'base'            => $base,
                'provider'        => Defaults::exchangeRatesProvider(),
                'status'          => 'error',
                'fetched_at_gmt'  => gmdate('Y-m-d H:i:s'),
                'expires_at_gmt'  => gmdate('Y-m-d H:i:s', time() + Defaults::exchangeRatesRefreshInterval()),
                'rates'           => [],
                'last_error'      => 'Invalid JSON response',
            ];
        }

        $apiBase = isset($decoded['base']) ? strtoupper((string) $decoded['base']) : $base;
        $apiRates = isset($decoded['rates']) && is_array($decoded['rates']) ? $decoded['rates'] : [];

        $normalizedRates = [];
        $normalizedRates[$base] = 1.0;

        foreach (Defaults::supportedCurrencies() as $currency) {
            $currency = strtoupper($currency);

            if ($currency === $base) {
                $normalizedRates[$currency] = 1.0;
                continue;
            }

            if (isset($apiRates[$currency]) && is_numeric($apiRates[$currency])) {
                $normalizedRates[$currency] = (float) $apiRates[$currency];
            }
        }

        return [
            'base'            => $apiBase,
            'provider'        => Defaults::exchangeRatesProvider(),
            'status'          => 'ok',
            'fetched_at_gmt'  => gmdate('Y-m-d H:i:s'),
            'expires_at_gmt'  => gmdate('Y-m-d H:i:s', time() + Defaults::exchangeRatesRefreshInterval()),
            'rates'           => $normalizedRates,
            'last_error'      => '',
        ];
    }
}