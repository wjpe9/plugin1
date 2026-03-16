<?php
declare(strict_types=1);

namespace CuPePricesSystem\Admin;

use CuPePricesSystem\Currency\CurrencyResolver;
use CuPePricesSystem\Currency\ExchangeRateService;
use CuPePricesSystem\Geo\GeoCountryResolver;
use CuPePricesSystem\Storage\ExchangeRateStorage;
use CuPePricesSystem\Support\CountryMap;
use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class SettingsPage
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu(): void
    {
        add_options_page(
            __('CuPe Prices System', 'cupe-prices-system'),
            __('CuPe Prices System', 'cupe-prices-system'),
            Defaults::adminCapability(),
            Defaults::adminPageSlug(),
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (!current_user_can(Defaults::adminCapability())) {
            return;
        }

        $this->handleActions();

        $storage = new ExchangeRateStorage();
        $payload = $storage->get();

        $geoResolver = new GeoCountryResolver();
        $countryCode = $geoResolver->resolveCountryCode();
        $geoCurrency = CountryMap::currencyForCountry($countryCode);

        $currencyResolver = new CurrencyResolver();
        $resolvedCurrency = $currencyResolver->resolve([]);

        $fetchedAt = is_array($payload) ? (string) ($payload['fetched_at_gmt'] ?? '') : '';
        $expiresAt = is_array($payload) ? (string) ($payload['expires_at_gmt'] ?? '') : '';
        $provider  = is_array($payload) ? (string) ($payload['provider'] ?? '') : '';
        $status    = is_array($payload) ? (string) ($payload['status'] ?? '') : '';
        $lastError = is_array($payload) ? (string) ($payload['last_error'] ?? '') : '';
        $rates     = is_array($payload) && isset($payload['rates']) && is_array($payload['rates']) ? $payload['rates'] : [];

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('CuPe Prices System', 'cupe-prices-system'); ?></h1>

            <?php if (isset($_GET['cupe_refreshed']) && $_GET['cupe_refreshed'] === '1') : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__('Exchange rates refreshed successfully.', 'cupe-prices-system'); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['cupe_geo_reset']) && $_GET['cupe_geo_reset'] === '1') : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__('Currency cookie cleared. Geolocation will be used again.', 'cupe-prices-system'); ?></p>
                </div>
            <?php endif; ?>

            <h2><?php echo esc_html__('Exchange Rate Status', 'cupe-prices-system'); ?></h2>

            <table class="widefat striped" style="max-width: 900px;">
                <tbody>
                    <tr>
                        <th style="width: 240px;"><?php echo esc_html__('Base currency', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html(Defaults::baseCurrency()); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Provider', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html($provider !== '' ? $provider : Defaults::exchangeRatesProvider()); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Status', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html($status !== '' ? $status : 'unknown'); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Last refresh (GMT)', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html($fetchedAt !== '' ? $fetchedAt : '—'); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Next refresh (GMT)', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html($expiresAt !== '' ? $expiresAt : '—'); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Last error', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html($lastError !== '' ? $lastError : '—'); ?></td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-top: 16px;">
                <a href="<?php echo esc_url($this->getRefreshUrl()); ?>" class="button button-primary">
                    <?php echo esc_html__('Refresh rates now', 'cupe-prices-system'); ?>
                </a>

                <a href="<?php echo esc_url($this->getResetGeoUrl()); ?>" class="button">
                    <?php echo esc_html__('Clear currency cookie', 'cupe-prices-system'); ?>
                </a>
            </p>

            <h2 style="margin-top: 32px;"><?php echo esc_html__('Stored Rates', 'cupe-prices-system'); ?></h2>

            <table class="widefat striped" style="max-width: 600px;">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Currency', 'cupe-prices-system'); ?></th>
                        <th><?php echo esc_html__('Rate from USD', 'cupe-prices-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rates)) : ?>
                        <?php foreach ($rates as $currency => $rate) : ?>
                            <tr>
                                <td><?php echo esc_html((string) $currency); ?></td>
                                <td><?php echo esc_html((string) $rate); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="2"><?php echo esc_html__('No stored rates found.', 'cupe-prices-system'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2 style="margin-top: 32px;"><?php echo esc_html__('Geolocation Debug', 'cupe-prices-system'); ?></h2>

            <table class="widefat striped" style="max-width: 900px;">
                <tbody>
                    <tr>
                        <th style="width: 240px;"><?php echo esc_html__('Detected country code', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html($countryCode); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Currency by geolocation', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html($geoCurrency); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Final resolved currency', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html($resolvedCurrency); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Cookie name', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html(Defaults::currencyCookieName()); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Cookie value', 'cupe-prices-system'); ?></th>
                        <td>
                            <?php
                            $cookieName = Defaults::currencyCookieName();
                            echo esc_html(isset($_COOKIE[$cookieName]) ? (string) $_COOKIE[$cookieName] : '—');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Cloudflare country header', 'cupe-prices-system'); ?></th>
                        <td><?php echo esc_html(isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? (string) $_SERVER['HTTP_CF_IPCOUNTRY'] : '—'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function handleActions(): void
    {
        if (!isset($_GET['page']) || $_GET['page'] !== Defaults::adminPageSlug()) {
            return;
        }

        if (isset($_GET['cupe_action'], $_GET['_wpnonce']) && $_GET['cupe_action'] === 'refresh_rates') {
            if (wp_verify_nonce((string) $_GET['_wpnonce'], 'cupe_refresh_rates')) {
                $service = new ExchangeRateService();
                $service->refreshRates();

                wp_safe_redirect($this->getBasePageUrl(['cupe_refreshed' => '1']));
                exit;
            }
        }

        if (isset($_GET['cupe_action'], $_GET['_wpnonce']) && $_GET['cupe_action'] === 'reset_geo_cookie') {
            if (wp_verify_nonce((string) $_GET['_wpnonce'], 'cupe_reset_geo_cookie')) {
                $this->clearCurrencyCookie();

                wp_safe_redirect($this->getBasePageUrl(['cupe_geo_reset' => '1']));
                exit;
            }
        }
    }

    private function clearCurrencyCookie(): void
    {
        $cookieName = Defaults::currencyCookieName();

        if (!headers_sent()) {
            setcookie(
                $cookieName,
                '',
                time() - HOUR_IN_SECONDS,
                COOKIEPATH ?: '/',
                COOKIE_DOMAIN,
                is_ssl(),
                true
            );
        }

        unset($_COOKIE[$cookieName]);
    }

    private function getRefreshUrl(): string
    {
        return wp_nonce_url(
            $this->getBasePageUrl(['cupe_action' => 'refresh_rates']),
            'cupe_refresh_rates'
        );
    }

    private function getResetGeoUrl(): string
    {
        return wp_nonce_url(
            $this->getBasePageUrl(['cupe_action' => 'reset_geo_cookie']),
            'cupe_reset_geo_cookie'
        );
    }

    /**
     * @param array<string,string> $extra
     */
    private function getBasePageUrl(array $extra = []): string
    {
        $args = array_merge([
            'page' => Defaults::adminPageSlug(),
        ], $extra);

        return add_query_arg($args, admin_url('options-general.php'));
    }
}