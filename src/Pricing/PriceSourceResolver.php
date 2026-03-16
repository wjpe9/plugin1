<?php
//src\Pricing\PriceSourceResolver.php

namespace CuPePricesSystem\Pricing;

use CuPePricesSystem\Integrations\ACF\ACFPriceSource;
use CuPePricesSystem\Integrations\Polylang\PolylangResolver;
use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class PriceSourceResolver
{
    private ACFPriceSource $acfSource;
    private PolylangResolver $polylangResolver;

    public function __construct()
    {
        $this->acfSource = new ACFPriceSource();
        $this->polylangResolver = new PolylangResolver();
    }

    /**
     * @param array<string,mixed> $atts
     * @return array<string,mixed>
     */
    public function resolve(array $atts): array
    {
        $manualPrice = $this->normalizeManualPrice($atts['price'] ?? null);

        if ($manualPrice !== null) {
            return [
                'price_usd' => $manualPrice,
                'source'    => 'manual',
                'post_id'   => 0,
                'field'     => '',
            ];
        }

        $postId = isset($atts['post_id']) ? (int) $atts['post_id'] : 0;
        if ($postId <= 0) {
            $postId = get_the_ID() ? (int) get_the_ID() : 0;
        }

        $field = isset($atts['field']) && is_string($atts['field']) && $atts['field'] !== ''
            ? $atts['field']
            : Defaults::defaultAcfField();

        $sourceLanguage = isset($atts['source_lang']) && is_string($atts['source_lang'])
            ? trim($atts['source_lang'])
            : '';

        $sourcePostId = $this->polylangResolver->resolveSourcePostId($postId, $sourceLanguage);
        $price = $this->acfSource->getPrice($sourcePostId, $field);

        return [
            'price_usd' => $price,
            'source'    => 'acf',
            'post_id'   => $sourcePostId,
            'field'     => $field,
        ];
    }

    private function normalizeManualPrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/[^\d,.\-]/', '', $value);

        if ($value === null || $value === '') {
            return null;
        }

        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace(',', '', $value);
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }
}