<?php
//src\Currency\ManualRateProvider.php

namespace CuPePricesSystem\Currency;

if (!defined('ABSPATH')) {
    exit;
}

final class ManualRateProvider
{
    public function getRateFromUsd(string $currency): float
    {
        $currency = strtoupper(trim($currency));

        $rates = [
            'USD' => 1.00,
            'EUR' => 0.92,
            'PEN' => 3.75,
            'BRL' => 4.95,
            'CLP' => 970.00,
        ];

        return isset($rates[$currency]) ? (float) $rates[$currency] : 1.00;
    }
}