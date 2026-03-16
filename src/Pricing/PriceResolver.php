<?php
//src\Pricing\PriceResolver.php

namespace CuPePricesSystem\Pricing;

use CuPePricesSystem\Currency\CurrencyResolver;
use CuPePricesSystem\Currency\ExchangeRateService;
use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class PriceResolver
{
    private PriceSourceResolver $sourceResolver;
    private CurrencyResolver $currencyResolver;
    private ExchangeRateService $exchangeRateService;

    public function __construct()
    {
        $this->sourceResolver = new PriceSourceResolver();
        $this->currencyResolver = new CurrencyResolver();
        $this->exchangeRateService = new ExchangeRateService();
    }

    /**
     * @param array<string,mixed> $atts
     * @return array<string,mixed>|null
     */
    public function resolve(array $atts): ?array
    {
        $source = $this->sourceResolver->resolve($atts);
        $priceUsd = $source['price_usd'] ?? null;

        if (!is_float($priceUsd) && !is_int($priceUsd)) {
            return null;
        }

        $targetCurrency = $this->currencyResolver->resolve($atts);
        $rate = $this->exchangeRateService->getRateFromUsd($targetCurrency);

        if ($rate <= 0) {
            $targetCurrency = Defaults::baseCurrency();
            $rate = 1.0;
        }

        $converted = (float) $priceUsd * $rate;

        return [
            'price_usd'       => (float) $priceUsd,
            'currency'        => $targetCurrency,
            'exchange_rate'   => $rate,
            'converted'       => $converted,
            'source'          => $source['source'] ?? 'unknown',
            'source_post_id'  => (int) ($source['post_id'] ?? 0),
            'source_field'    => (string) ($source['field'] ?? ''),
            'label'           => isset($atts['label']) ? (string) $atts['label'] : '',
            'suffix'          => isset($atts['suffix']) ? (string) $atts['suffix'] : '',
            'context'         => isset($atts['context']) ? (string) $atts['context'] : 'default',
            'show_symbol'     => $this->toBool($atts['show_symbol'] ?? 'true'),
            'show_code'       => $this->toBool($atts['show_code'] ?? 'false'),
            'round'           => isset($atts['round']) ? (string) $atts['round'] : 'ceil',
            'decimals'        => isset($atts['decimals']) ? (int) $atts['decimals'] : 0,
            'class'           => isset($atts['class']) ? (string) $atts['class'] : '',
        ];
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower((string) $value);
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}