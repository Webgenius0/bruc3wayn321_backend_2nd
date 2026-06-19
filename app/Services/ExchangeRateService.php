<?php

namespace App\Services;

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
        return Cache::remember(
            "exchange_rate_{$base}",
            now()->addHour(),
            function () use ($base) {

                $response = Http::timeout(15)
                    ->get(
                        "https://v6.exchangerate-api.com/v6/{$this->apiKey}/latest/{$base}"
                    );

                if (!$response->successful()) {
                    throw new \Exception('Exchange unavailable');
                }

                return $response->json();
            }
        );
    }

    public function convert(
        string $from,
        string $to,
        float $amount
    ): array {

        $rates = $this->getRates($from);

        $rate = $rates['conversion_rates'][$to] ?? 0;

        return [
            'rate' => $rate,
            'amount' => round($amount * $rate, 2)
        ];
    }
}
