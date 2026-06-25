<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.exchange_rate.key');
    }

    public function getRates(string $base): array
    {
        $base = strtoupper($base);

        return Cache::remember(
            "exchange_rate_{$base}",
            now()->addHours(6),
            function () use ($base) {

                $response = Http::timeout(15)
                    ->retry(3, 500)
                    ->get(
                        "https://v6.exchangerate-api.com/v6/{$this->apiKey}/latest/{$base}"
                    );

                if (!$response->successful()) {
                    throw new Exception(
                        "Unable to fetch exchange rates for {$base}"
                    );
                }

                $data = $response->json();

                if (
                    !isset($data['result']) ||
                    $data['result'] !== 'success'
                ) {
                    throw new Exception(
                        $data['error-type'] ?? 'Exchange rate API error'
                    );
                }

                return $data;
            }
        );
    }

    public function convert(
        string $from,
        string $to,
        float $amount
    ): array {

        $from = strtoupper($from);
        $to = strtoupper($to);

        $rates = $this->getRates($from);

        $rate = $rates['conversion_rates'][$to] ?? null;

        if (!$rate) {
            throw new Exception(
                "Exchange rate not found for {$from} -> {$to}"
            );
        }

        return [
            'from' => $from,
            'to' => $to,
            'rate' => $rate,
            'original_amount' => $amount,
            'converted_amount' => round($amount * $rate, 2),
            'last_update' => $rates['time_last_update_utc'] ?? null,
        ];
    }

    public function clearCache(string $base): void
    {
        Cache::forget(
            'exchange_rate_' . strtoupper($base)
        );
    }
}
