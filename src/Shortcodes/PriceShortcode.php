<?php
//src\Shortcodes\PriceShortcode.php

namespace CuPePricesSystem\Shortcodes;

use CuPePricesSystem\Pricing\PriceFormatter;
use CuPePricesSystem\Pricing\PriceRenderer;
use CuPePricesSystem\Pricing\PriceResolver;

if (!defined('ABSPATH')) {
    exit;
}

final class PriceShortcode
{
    public function register(): void
    {
        add_shortcode('cupe_price', [$this, 'render']);
    }

    /**
     * @param array<string,mixed> $atts
     */
    public function render(array $atts = [], ?string $content = null): string
    {
        $atts = shortcode_atts([
            'price'       => '',
            'field'       => 'precio',
            'post_id'     => 0,
            'source_lang' => '',
            'currency'    => '',
            'label'       => '',
            'suffix'      => '',
            'context'     => 'default',
            'show_symbol' => 'true',
            'show_code'   => 'false',
            'round'       => 'ceil',
            'decimals'    => 0,
            'class'       => '',
        ], $atts, 'cupe_price');

        $resolver = new PriceResolver();
        $resolved = $resolver->resolve($atts);

        if ($resolved === null) {
            return '';
        }

        $formatter = new PriceFormatter();
        $formatted = $formatter->format($resolved);

        $renderer = new PriceRenderer();
        return $renderer->render($formatted);
    }
}