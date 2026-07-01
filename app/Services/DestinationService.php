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

    public function list($request = null, float $lat, float $lng)
    {
        $savedPlaceIds = auth()->check()
            ? UserDestination::where('user_id', auth()->id())
                ->pluck('place_id')
                ->toArray()
            : [];

        $search = $request['search'] ?? 'tourist attractions';
        $items  = min((int)($request['items'] ?? 20), 100);

        $results = [];

        for ($start = 0; $start < $items; $start += 20) {

            $response = Http::get('https://serpapi.com/search.json', [
                'engine'  => 'google_maps',
                'type'    => 'search',
                'q'       => $search,
                'll'      => "@{$lat},{$lng},14z",
                'start'   => $start,
                'api_key' => config('services.serpapi.key'),
            ])->json();

            if (empty($response['local_results'])) {
                break;
            }

            $results = array_merge(
                $results,
                $response['local_results']
            );

            if (count($response['local_results']) < 20) {
                break;
            }
        }

        return collect($results)
            ->unique('place_id')
            ->take($items)
            ->map(function ($item) use ($savedPlaceIds) {

                return [

                    'place_id' => $item['place_id'] ?? '',

                    'title' => $item['title'] ?? '',

                    'subtitle' => $item['address'] ?? '',

                    'description' => $item['address'] ?? '',

                    'rating' => (string)($item['rating'] ?? '0'),

                    'image' => $item['thumbnail'] ?? null,

                    'latitude' => isset($item['gps_coordinates']['latitude'])
                        ? (string)$item['gps_coordinates']['latitude']
                        : null,

                    'longitude' => isset($item['gps_coordinates']['longitude'])
                        ? (string)$item['gps_coordinates']['longitude']
                        : null,

                    'is_saved' => in_array(
                        $item['place_id'] ?? '',
                        $savedPlaceIds
                    ),
                ];

            })
            ->values();
    }

    public function details(string $placeId)
    {
        $destination = $this->getDestinationDetails($placeId);
        // $destination = Cache::remember(
        //     "destination_details_{$placeId}",
        //     now()->addDays(7),
        //     fn() => $this->getDestinationDetails($placeId)
        // );



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

        // $weather = $this->openweather->currentByCoordinates($place['geometry']['location']['lat'], $place['geometry']['location']['lng']);
        // dd($place);

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
        // $response = Http::get(
        //     'https://maps.googleapis.com/maps/api/place/details/json',
        //     [
        //         'place_id' => $placeId,
        //         'key' => config('services.google.places_api_key'),
        //     ]
        // )->json();
        $response = Http::get('https://serpapi.com/search.json', [
    'engine'  => 'google_maps',
    'type'    => 'place',
    'place_id'=> $placeId,
    'api_key' => config('services.serpapi.key'),
])->json();

        $place = $response['result'] ?? [];

        $photo = null;

        if (!empty($place['photos'][0]['photo_reference'])) {
            $photo =
                "https://maps.googleapis.com/maps/api/place/photo"
                . "?maxwidth=1200"
                . "&photo_reference={$place['photos'][0]['photo_reference']}"
                . "&key=" . config('services.google.places_api_key');
        }

$place = $response['place_results'] ?? [];

return [

    'place_id' => $place['place_id'] ?? '',

    'name' => $place['title'] ?? '',

    'description' => $place['description'] ?? ($place['address'] ?? ''),

    'image' => $place['thumbnail'] ?? null,

    'rating' => (string)($place['rating'] ?? '0'),

    'address' => $place['address'] ?? '',

    'phone' => $place['phone'] ?? '',

    'website' => $place['website'] ?? '',

    'latitude' => $place['gps_coordinates']['latitude'] ?? null,

    'longitude' => $place['gps_coordinates']['longitude'] ?? null,

    'cost_of_living' => '',
    'visa' => '',
    'food' => '',
    'culture' => '',
    'transport' => '',
    'safety' => '',
];
        // return [
        //     'place_id' => $place['place_id'] ?? null,
        //     'name' => $place['name'] ?? '',
        //     'description' => $place['editorial_summary']['overview']
        //         ?? $place['formatted_address']
        //         ?? '',
        //     'image' => $photo,
        //     'rating' => isset($place['rating'])
        //         ? (string) $place['rating']
        //         : '0',
        //     'address' => $place['formatted_address'] ?? '',
        //     'phone' => $place['formatted_phone_number'] ?? '',
        //     'website' => $place['website'] ?? '',
        //     'latitude' => isset($place['geometry']['location']['lat'])
        //         ? (string) $place['geometry']['location']['lat']
        //         : null,
        //     'longitude' => isset($place['geometry']['location']['lng'])
        //         ? (string) $place['geometry']['location']['lng']
        //         : null,

        //     // Mock values for UI
        //     'cost_of_living' => '',
        //     'visa' => '',
        //     'food' => '',
        //     'culture' => '',
        //     'transport' => '',
        //     'safety' => '',
        // ];
    }
}
