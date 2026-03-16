<?php
//src\Integrations\Polylang\PolylangResolver.php

namespace CuPePricesSystem\Integrations\Polylang;

if (!defined('ABSPATH')) {
    exit;
}

final class PolylangResolver
{
    public function resolveSourcePostId(int $postId, string $sourceLanguage = ''): int
    {
        if ($postId <= 0) {
            return 0;
        }

        if (!function_exists('pll_get_post')) {
            return $postId;
        }

        $lang = trim($sourceLanguage);

        if ($lang !== '') {
            $translatedId = pll_get_post($postId, $lang);
            if (!empty($translatedId)) {
                return (int) $translatedId;
            }
        }

        return $postId;
    }
}