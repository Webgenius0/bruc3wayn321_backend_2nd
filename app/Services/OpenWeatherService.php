<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

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

    /**
     * Get current weather by coordinates.
     */
    public function currentByCoordinates(
        float $latitude,
        float $longitude
    ): array {
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

    /**
     * Get 5-day / 3-hour forecast.
     */
    public function forecast(
        float $latitude,
        float $longitude
    ): array {
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
}
