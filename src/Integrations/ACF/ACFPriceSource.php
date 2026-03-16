<?php
//src\Integrations\ACF\ACFPriceSource.php

namespace CuPePricesSystem\Integrations\ACF;

if (!defined('ABSPATH')) {
    exit;
}

final class ACFPriceSource
{
    public function getPrice(int $postId, string $field = 'precio'): ?float
    {
        if ($postId <= 0 || $field === '') {
            return null;
        }

        $raw = null;

        if (function_exists('get_field')) {
            $raw = get_field($field, $postId);
        }

        if ($raw === null || $raw === '' || $raw === false) {
            $raw = get_post_meta($postId, $field, true);
        }

        if ($raw === null || $raw === '' || $raw === false) {
            return null;
        }

        return $this->normalizeToFloat($raw);
    }

    private function normalizeToFloat($value): ?float
    {
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