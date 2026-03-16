<?php
//src\Pricing\PriceFormatter.php

namespace CuPePricesSystem\Pricing;

use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class PriceFormatter
{
    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function format(array $data): array
    {
        $amount = isset($data['converted']) ? (float) $data['converted'] : 0.0;
        $currency = isset($data['currency']) ? strtoupper((string) $data['currency']) : Defaults::baseCurrency();
        $roundMode = isset($data['round']) ? (string) $data['round'] : 'ceil';
        $decimals = isset($data['decimals']) ? (int) $data['decimals'] : 0;

        $amount = $this->applyRounding($amount, $roundMode);

        $format = $this->getFormatForCurrency($currency);

        $formatted = number_format(
            $amount,
            $decimals,
            $format['decimal_separator'],
            $format['thousands_separator']
        );

        $data['amount_rounded'] = $amount;
        $data['formatted_amount'] = $formatted;
        $data['symbol'] = $format['symbol'];
        $data['symbol_position'] = $format['symbol_position'];

        return $data;
    }

    private function applyRounding(float $amount, string $roundMode): float
    {
        switch (strtolower($roundMode)) {
            case 'floor':
                return (float) floor($amount);

            case 'round':
                return (float) round($amount);

            case 'none':
                return $amount;

            case 'ceil':
            default:
                return (float) ceil($amount);
        }
    }

    /**
     * @return array<string,string>
     */
    private function getFormatForCurrency(string $currency): array
    {
        $map = [
            'USD' => [
                'symbol' => '$',
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousands_separator' => ',',
            ],
            'EUR' => [
                'symbol' => '€',
                'symbol_position' => 'before',
                'decimal_separator' => ',',
                'thousands_separator' => '.',
            ],
            'PEN' => [
                'symbol' => 'S/',
                'symbol_position' => 'before',
                'decimal_separator' => '.',
                'thousands_separator' => ',',
            ],
            'BRL' => [
                'symbol' => 'R$',
                'symbol_position' => 'before',
                'decimal_separator' => ',',
                'thousands_separator' => '.',
            ],
            'CLP' => [
                'symbol' => '$',
                'symbol_position' => 'before',
                'decimal_separator' => ',',
                'thousands_separator' => '.',
            ],
        ];

        return $map[$currency] ?? $map['USD'];
    }
}