<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenWeatherService
{
    protected string $baseUrl = 'https://api.openweathermap.org/data/2.5';

    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.api_key');
    }

    /**
     * Get current weather by city name.
     */
    public function currentByCity(string $city): array
    {
        return Cache::remember(
            'weather_city_' . md5(strtolower($city)),
            now()->addHour(),
            function () use ($city) {

                $response = Http::get(
                    "{$this->baseUrl}/weather",
                    [
                        'q'     => $city,
                        'appid' => $this->apiKey,
                        'units' => 'metric',
                    ]
                );

                $response->throw();

                return $response->json();
            }
        );
    }

    /**
     * Get current weather by coordinates.
     */
    public function currentByCoordinates(
        float $latitude,
        float $longitude
    ): array {
        return Cache::remember(
            'weather_coords_' . md5($latitude . '_' . $longitude),
            now()->addHour(),
            function () use ($latitude, $longitude) {

                $response = Http::get(
                    "{$this->baseUrl}/weather",
                    [
                        'lat'   => $latitude,
                        'lon'   => $longitude,
                        'appid' => $this->apiKey,
                        'units' => 'metric',
                    ]
                );

                $response->throw();

                return $response->json();
            }
        );
    }

    /**
     * Get 5-day / 3-hour forecast.
     */
    public function forecast(
        float $latitude,
        float $longitude
    ): array {
        return Cache::remember(
            'weather_forecast_' . md5($latitude . '_' . $longitude),
            now()->addHours(3),
            function () use ($latitude, $longitude) {

                $response = Http::get(
                    "{$this->baseUrl}/forecast",
                    [
                        'lat'   => $latitude,
                        'lon'   => $longitude,
                        'appid' => $this->apiKey,
                        'units' => 'metric',
                    ]
                );

                $response->throw();

                return $response->json();
            }
        );
    }

    /**
     * Clear weather cache manually.
     */
    public function clearCoordinatesCache(
        float $latitude,
        float $longitude
    ): void {
        Cache::forget(
            'weather_coords_' . md5($latitude . '_' . $longitude)
        );

        Cache::forget(
            'weather_forecast_' . md5($latitude . '_' . $longitude)
        );
    }
}
