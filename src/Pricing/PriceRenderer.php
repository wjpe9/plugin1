<?php
//src\Pricing\PriceRenderer.php

namespace CuPePricesSystem\Pricing;

if (!defined('ABSPATH')) {
    exit;
}

final class PriceRenderer
{
    /**
     * @param array<string,mixed> $data
     */
    public function render(array $data): string
    {
        wp_enqueue_style('cupe-prices-public');

        $template = CUPE_PRICES_SYSTEM_PATH . 'templates/price.php';

        if (!is_readable($template)) {
            return '';
        }

        ob_start();
        $priceData = $data;
        include $template;
        return (string) ob_get_clean();
    }
}