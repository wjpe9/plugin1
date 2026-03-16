<?php
declare(strict_types=1);

namespace CuPePricesSystem;

use CuPePricesSystem\Admin\SettingsPage;
use CuPePricesSystem\Currency\ExchangeRateCron;
use CuPePricesSystem\Shortcodes\PriceShortcode;

if (!defined('ABSPATH')) {
    exit;
}

final class Plugin
{
    public function boot(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
        add_action('init', [$this, 'registerShortcodes']);

        $cron = new ExchangeRateCron();
        $cron->register();

        $admin = new SettingsPage();
        $admin->register();
    }

    public function activate(): void
    {
        $cron = new ExchangeRateCron();
        $cron->register();
        $cron->schedule();
        $cron->runInitialRefresh();
    }

    public function deactivate(): void
    {
        $cron = new ExchangeRateCron();
        $cron->unschedule();
    }

    public function registerAssets(): void
    {
        wp_register_style(
            'cupe-prices-public',
            CUPE_PRICES_SYSTEM_URL . 'assets/css/cups-prices-public.css',
            [],
            CUPE_PRICES_SYSTEM_VERSION
        );
    }

    public function registerShortcodes(): void
    {
        $shortcode = new PriceShortcode();
        $shortcode->register();
    }
}