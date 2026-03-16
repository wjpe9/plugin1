<?php
//src\Currency\ExchangeRateCron.php

namespace CuPePricesSystem\Currency;

if (!defined('ABSPATH')) {
    exit;
}

final class ExchangeRateCron
{
    public const CRON_HOOK = 'cupe_prices_refresh_exchange_rates';
    public const CRON_SCHEDULE = 'cupe_prices_every_48_hours';

    public function register(): void
    {
        add_filter('cron_schedules', [$this, 'addCustomSchedule']);
        add_action(self::CRON_HOOK, [$this, 'handle']);
    }

    /**
     * @param array<string,mixed> $schedules
     * @return array<string,mixed>
     */
    public function addCustomSchedule(array $schedules): array
    {
        if (!isset($schedules[self::CRON_SCHEDULE])) {
            $schedules[self::CRON_SCHEDULE] = [
                'interval' => 2 * DAY_IN_SECONDS,
                'display'  => __('Every 48 Hours', 'cupe-prices-system'),
            ];
        }

        return $schedules;
    }

    public function schedule(): void
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + 60, self::CRON_SCHEDULE, self::CRON_HOOK);
        }
    }

    public function unschedule(): void
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);

        while ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
            $timestamp = wp_next_scheduled(self::CRON_HOOK);
        }
    }

    public function handle(): void
    {
        $service = new ExchangeRateService();
        $service->refreshRates();
    }

    public function runInitialRefresh(): void
    {
        $service = new ExchangeRateService();
        $service->refreshRates();
    }
}