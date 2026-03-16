<?php


/**
 * Plugin Name: CuPe Prices System
 * Description: Sistema de precios base en USD con soporte ACF, shortcode, conversión de moneda y compatibilidad inicial con Polylang.
 * Version: 0.1.0
 * Author: TQP Slave (; . ;)
 * Text Domain: cupe-prices-system
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CUPE_PRICES_SYSTEM_VERSION', '0.1.0');
define('CUPE_PRICES_SYSTEM_FILE', __FILE__);
define('CUPE_PRICES_SYSTEM_PATH', plugin_dir_path(__FILE__));
define('CUPE_PRICES_SYSTEM_URL', plugin_dir_url(__FILE__));

require_once CUPE_PRICES_SYSTEM_PATH . 'src/Autoloader.php';

\CuPePricesSystem\Autoloader::register();

register_activation_hook(__FILE__, static function (): void {
    $plugin = new \CuPePricesSystem\Plugin();
    $plugin->activate();
});

register_deactivation_hook(__FILE__, static function (): void {
    $plugin = new \CuPePricesSystem\Plugin();
    $plugin->deactivate();
});

add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain('cupe-prices-system', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $plugin = new \CuPePricesSystem\Plugin();
    $plugin->boot();
});