<?php

namespace App\Services;

use App\Models\UserDestination;
use App\Repositories\DestinationRepository;
use App\Services\OpenWeatherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DestinationService
{
    public function __construct(
        protected DestinationRepository $destinationRepository
    ) {}

    public function list($request = null, float $lat, float $lng, $items = 100)
    {
        $savedPlaceIds = auth()->check()
            ? UserDestination::where('user_id', auth()->id())
            ->pluck('place_id')
            ->toArray()
            : [];

        // dd(config('services.google'));
        $search = $request['search'] ?? 'tourist attractions';
        $limit  = min((int)($items ?? 20), 100);

        $results = [];

        for ($start = 0; $start < $limit; $start += 100) {


            $response = Http::get(
                'https://maps.googleapis.com/maps/api/place/textsearch/json',
                [
                    'query' => $request['search'] ?? 'popular places',
                    'tourist attractions, landmarks, parks, museums, beaches, viewpoints, historical places, hotels, resorts, restaurants, cafes, local food, shopping malls, markets, entertainment, theme parks, zoos, aquariums, cultural centers, art galleries',
                    'location' => "{$lat},{$lng}",
                    'radius' => 5000,
                    'key' => config('services.google.places_api_key'),
                ]
            )->json();

            if (empty($response['results'])) {
                break;
            }

            $results = array_merge(
                $results,
                $response['results']
            );

            // if (count($response['results']) < 20) {
            //     break;
            // }
        }

        return collect($results ?? [])
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
            fn() => $this->getDestinationDetails($placeId)
        );



        $savedPlaceIds = auth()->check()
            ? UserDestination::where('user_id', auth()->id())
            ->pluck('place_id')
            ->toArray()
            : [];

        $destination['is_saved'] = in_array(
            $placeId,
            $savedPlaceIds
        );

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

        return [
            'place_id' => $place['place_id'] ?? null,
            'name' => $place['name'] ?? '',
            'description' => $place['editorial_summary']['overview']
                ?? $place['formatted_address']
                ?? '',
            'image' => $photo,
            'rating' => isset($place['rating'])
                ? (string) $place['rating']
                : '0',
            // 'address' => $place['formatted_address'] ?? '',
            // 'phone' => $place['formatted_phone_number'] ?? '',
            'website' => $place['website'] ?? '',
            'latitude' => isset($place['geometry']['location']['lat'])
                ? (string) $place['geometry']['location']['lat']
                : null,
            'longitude' => isset($place['geometry']['location']['lng'])
                ? (string) $place['geometry']['location']['lng']
                : null,
            // Mock values for UI
            'cost_of_living' => '',
            'visa' => '',
            'food' => '',
            'culture' => '',
            'transport' => '',
            'safety' => '',
        ];
    }


    public function getDestinationDetails(string $placeId)
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


        return [
            'place_id' => $place['place_id'] ?? null,
            'name' => $place['name'] ?? '',
            'description' => $place['editorial_summary']['overview']
                ?? $place['formatted_address']
                ?? '',
            'image' => $photo,
            'rating' => isset($place['rating'])
                ? (string) $place['rating']
                : '0',
            'address' => $place['formatted_address'] ?? '',
            'phone' => $place['formatted_phone_number'] ?? '',
            'website' => $place['website'] ?? '',
            'latitude' => isset($place['geometry']['location']['lat'])
                ? (string) $place['geometry']['location']['lat']
                : null,
            'longitude' => isset($place['geometry']['location']['lng'])
                ? (string) $place['geometry']['location']['lng']
                : null,

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
