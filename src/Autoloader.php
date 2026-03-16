<?php
//src\Autoloader.php

namespace CuPePricesSystem;

if (!defined('ABSPATH')) {
    exit;
}

final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        $prefix = __NAMESPACE__ . '\\';

        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        $file = CUPE_PRICES_SYSTEM_PATH . 'src/' . $relativePath;

        if (is_readable($file)) {
            require_once $file;
        }
    }
}