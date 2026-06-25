<?php

namespace App\Services;

use App\Models\UserDestination;
use App\Repositories\DestinationRepository;
use App\Services\OpenWeatherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DestinationService
{
    protected $openweather;
    public function __construct(
        protected DestinationRepository $destinationRepository,
        OpenWeatherService $openweather,
    ) {
        $this->openweather = $openweather;
    }

    public function list(float $lat, float $lng)
    {
        $savedPlaceIds = auth()->check()
            ? UserDestination::where('user_id', auth()->id())
            ->pluck('place_id')
            ->toArray()
            : [];

        $response = Http::get(
            'https://maps.googleapis.com/maps/api/place/textsearch/json',
            [
                'query' => 'tourist attractions',
                'location' => "{$lat},{$lng}",
                'radius' => 50000,
                'key' => config('services.google.places_api_key'),
            ]
        )->json();

        return collect($response['results'] ?? [])
            ->map(function ($item) use ($savedPlaceIds) {

                $photo = null;

                if (!empty($item['photos'][0]['photo_reference'])) {
                    $photo =
                        "https://maps.googleapis.com/maps/api/place/photo"
                        . "?maxwidth=1200"
                        . "&photo_reference={$item['photos'][0]['photo_reference']}"
                        . "&key=" . config('services.google.places_api_key');
                }

                return [
                    'place_id' => $item['place_id'],
                    'title' => $item['name'],
                    'subtitle' => $item['formatted_address'] ?? '',
                    'description' => $item['formatted_address'] ?? '',
                    'rating' => isset($item['rating'])
                        ? (string) $item['rating']
                        : '0',
                    'image' => $photo,
                    'latitude' => isset($item['geometry']['location']['lat'])
                        ? (string) $item['geometry']['location']['lat']
                        : null,

                    'longitude' => isset($item['geometry']['location']['lng'])
                        ? (string) $item['geometry']['location']['lng']
                        : null,
                    // Saved status
                    'is_saved' => in_array(
                        $item['place_id'],
                        $savedPlaceIds
                    ),
                ];
            })
            ->values();
    }

    public function details(string $placeId)
    {
        $destination = Cache::remember(
            "destination_details_{$placeId}",
            now()->addDays(7),
            fn() => $this->getDetails($placeId)
        );

        $weather = null;



        if (
            !empty($destination['latitude']) &&
            !empty($destination['longitude'])
        ) {
            $weather = Cache::remember(
                "weather_{$destination['latitude']}_{$destination['longitude']}",
                now()->addHour(),
                fn() => $this->openweather->currentByCoordinates(
                    $destination['latitude'],
                    $destination['longitude']
                )
            );
        }

        $savedPlaceIds = auth()->check()
            ? UserDestination::where('user_id', auth()->id())
            ->pluck('place_id')
            ->toArray()
            : [];

        $destination['is_saved'] = in_array(
            $placeId,
            $savedPlaceIds
        );

        $destination['weather'] = $weather;

        return $destination;
    }

    public function getDetails(string $placeId)
    {
        $response = Http::get(
            'https://maps.googleapis.com/maps/api/place/details/json',
            [
                'place_id' => $placeId,
                'key' => config('services.google.places_api_key'),
            ]
        )->json();

        $place = $response['result'] ?? [];

        $photo = null;

        if (!empty($place['photos'][0]['photo_reference'])) {
            $photo =
                "https://maps.googleapis.com/maps/api/place/photo"
                . "?maxwidth=1200"
                . "&photo_reference={$place['photos'][0]['photo_reference']}"
                . "&key=" . config('services.google.places_api_key');
        }

        $weather = $this->openweather->currentByCoordinates($place['geometry']['location']['lat'], $place['geometry']['location']['lng']);

        return [
            'place_id' => $place['place_id'] ?? null,
            'name' => $place['name'] ?? '',
            'description' => $place['editorial_summary']['overview']
                ?? $place['formatted_address']
                ?? '',
            'image' => $photo,
            'rating' => isset($item['rating'])
                ? (string) $item['rating']
                : '0',
            'address' => $place['formatted_address'] ?? '',
            'phone' => $place['formatted_phone_number'] ?? '',
            'website' => $place['website'] ?? '',
            'latitude' => isset($item['geometry']['location']['lat'])
                ? (string) $item['geometry']['location']['lat']
                : null,
            'longitude' => isset($item['geometry']['location']['lng'])
                ? (string) $item['geometry']['location']['lng']
                : null,
            'weather' => $weather,

            // Mock values for UI
            'cost_of_living' => '',
            'visa' => '',
            'food' => '',
            'culture' => '',
            'transport' => '',
            'safety' => '',

        ];
    }
}
