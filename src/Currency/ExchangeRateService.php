<?php
//src\Currency\ExchangeRateService.php
namespace CuPePricesSystem\Currency;

use CuPePricesSystem\Currency\ApiRateProvider;
use CuPePricesSystem\Storage\ExchangeRateStorage;
use CuPePricesSystem\Support\Defaults;

if (!defined('ABSPATH')) {
    exit;
}

final class ExchangeRateService
{
    private ManualRateProvider $manualProvider;
    private ApiRateProvider $apiProvider;
    private ExchangeRateStorage $storage;

    public function __construct()
    {
        $this->manualProvider = new ManualRateProvider();
        $this->apiProvider    = new ApiRateProvider();
        $this->storage        = new ExchangeRateStorage();
    }

    public function getRateFromUsd(string $currency): float
    {
        $currency = strtoupper(trim($currency));

        if (!in_array($currency, Defaults::supportedCurrencies(), true)) {
            return 1.0;
        }

        $payload = $this->getRatesPayload();
        $rates = $payload['rates'] ?? [];

        if (!is_array($rates)) {
            return 1.0;
        }

        if (!isset($rates[$currency]) || !is_numeric($rates[$currency])) {
            return 1.0;
        }

        return (float) $rates[$currency];
    }

    /**
     * @return array<string,mixed>
     */
    public function getRatesPayload(): array
    {
        $stored = $this->storage->get();

        if ($this->storage->hasUsableRates($stored) && !$this->storage->isExpired($stored)) {
            return $stored;
        }

        $fresh = $this->refreshRates();

        if ($this->storage->hasUsableRates($fresh)) {
            return $fresh;
        }

        if ($this->storage->hasUsableRates($stored)) {
            return $stored;
        }

        return $this->manualProvider->getRatesPayload();
    }

    /**
     * Fuerza actualización desde la API.
     *
     * @return array<string,mixed>
     */
    public function refreshRates(): array
    {
        $apiPayload = $this->apiProvider->fetchRates();

        if (is_array($apiPayload) && ($apiPayload['status'] ?? '') === 'ok' && $this->hasUsableRatesArray($apiPayload)) {
            $this->storage->save($apiPayload);
            return $apiPayload;
        }

        $stored = $this->storage->get();

        if ($this->storage->hasUsableRates($stored)) {
            if (is_array($apiPayload) && !empty($apiPayload['last_error'])) {
                $stored['last_error'] = (string) $apiPayload['last_error'];
                $stored['status'] = 'stale';
                $this->storage->save($stored);
                return $stored;
            }

            return $stored;
        }

        $fallback = $this->manualProvider->getRatesPayload();

        if (is_array($apiPayload) && !empty($apiPayload['last_error'])) {
            $fallback['last_error'] = (string) $apiPayload['last_error'];
        }

        $this->storage->save($fallback);

        return $fallback;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function hasUsableRatesArray(array $payload): bool
    {
        if (!isset($payload['rates']) || !is_array($payload['rates']) || empty($payload['rates'])) {
            return false;
        }

        return isset($payload['rates']['USD']) && is_numeric($payload['rates']['USD']);
    }
}