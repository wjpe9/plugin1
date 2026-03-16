<?php
//templates\price.php

if (!defined('ABSPATH')) {
    exit;
}

/** @var array<string,mixed> $priceData */

$currency = strtolower((string) ($priceData['currency'] ?? 'usd'));
$context  = sanitize_html_class((string) ($priceData['context'] ?? 'default'));
$extra    = trim((string) ($priceData['class'] ?? ''));

$classes = [
    'cupe-price',
    'cupe-price--currency-' . sanitize_html_class($currency),
    'cupe-price--context-' . $context,
    'cupe-price--source-' . sanitize_html_class((string) ($priceData['source'] ?? 'unknown')),
];

if ($extra !== '') {
    $classes[] = $extra;
}

$label = (string) ($priceData['label'] ?? '');
$suffix = (string) ($priceData['suffix'] ?? '');
$showSymbol = !empty($priceData['show_symbol']);
$showCode = !empty($priceData['show_code']);
$symbol = (string) ($priceData['symbol'] ?? '$');
$symbolPosition = (string) ($priceData['symbol_position'] ?? 'before');
$formattedAmount = (string) ($priceData['formatted_amount'] ?? '');
$currencyCode = strtoupper((string) ($priceData['currency'] ?? 'USD'));
?>
<div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
    <?php if ($label !== '') : ?>
        <span class="cupe-price__label"><?php echo esc_html($label); ?></span>
    <?php endif; ?>

    <span class="cupe-price__main">
        <?php if ($showSymbol && $symbolPosition === 'before') : ?>
            <span class="cupe-price__symbol"><?php echo esc_html($symbol); ?></span>
        <?php endif; ?>

        <span class="cupe-price__amount"><?php echo esc_html($formattedAmount); ?></span>

        <?php if ($showSymbol && $symbolPosition === 'after') : ?>
            <span class="cupe-price__symbol"><?php echo esc_html($symbol); ?></span>
        <?php endif; ?>

        <?php if ($showCode) : ?>
            <span class="cupe-price__code"><?php echo esc_html($currencyCode); ?></span>
        <?php endif; ?>
    </span>

    <?php if ($suffix !== '') : ?>
        <span class="cupe-price__suffix"><?php echo esc_html($suffix); ?></span>
    <?php endif; ?>
</div>