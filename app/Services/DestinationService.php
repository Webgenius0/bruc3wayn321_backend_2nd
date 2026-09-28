<?php

namespace App\Services;

use App\Models\UserDestination;
use App\Repositories\DestinationRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DestinationService
{
    /**
     * SerpAPI base URL for Google Maps engine.
     */
    private const SERPAPI_URL = 'https://serpapi.com/search.json';

    public function __construct(
        protected DestinationRepository $destinationRepository
    ) {}

    /**
     * Search places list via SerpAPI Google Maps engine.
     *
     * @param array|null  $request  Incoming request params (e.g. ['search' => 'restaurants'])
     * @param float       $lat      Latitude
     * @param float       $lng      Longitude
     * @param int         $items    Max number of results to return (capped at 100)
     */
    public function list($request = null, float $lat, float $lng, $items = 100)
    {
        $savedPlaceIds = auth()->check()
            ? UserDestination::where('user_id', auth()->id())
            ->pluck('place_id')
            ->toArray()
            : [];

        $search = $request['search'] ?? 'tourist attractions';
        $limit  = min((int)($items ?? 20), 100);

        // SerpAPI Google Maps uses "ll" for GPS in the format "@lat,lng,zoom"
        $ll = "@{$lat},{$lng},14z";

        $results = [];
        $start   = 0;

        // SerpAPI paginates via "start" parameter (each page = 20 results)
        while (count($results) < $limit) {
            $response = Http::get(self::SERPAPI_URL, [
                'engine'  => 'google_maps',
                'q'       => $search,
                'll'      => $ll,
                'type'    => 'search',
                'start'   => $start,
                'api_key' => config('services.serpapi.key'),
            ])->json();

            $places = $response['local_results'] ?? [];

            if (empty($places)) {
                break;
            }

            $results = array_merge($results, $places);
            $start  += count($places);

            // Stop if there's no next-page token
            if (empty($response['serpapi_pagination']['next'])) {
                break;
            }

            // Safety: avoid over-fetching
            if ($start >= $limit) {
                break;
            }
        }

        return collect(array_slice($results, 0, $limit))
            ->map(function ($item) use ($savedPlaceIds) {
                $thumbnail = $item['thumbnail'] ?? null;
                $placeId   = $item['place_id'] ?? null;

                return [
                    'place_id'    => $placeId,
                    'title'       => $item['title'] ?? '',
                    'subtitle'    => $item['address'] ?? '',
                    'description' => $item['address'] ?? '',
                    'rating'      => isset($item['rating'])
                        ? (string) $item['rating']
                        : '0',
                    'image'       => $thumbnail,
                    'latitude'    => isset($item['gps_coordinates']['latitude'])
                        ? (string) $item['gps_coordinates']['latitude']
                        : null,
                    'longitude'   => isset($item['gps_coordinates']['longitude'])
                        ? (string) $item['gps_coordinates']['longitude']
                        : null,
                    'is_saved'    => in_array($placeId, $savedPlaceIds),
                ];
            })
            ->values();
    }

    /**
     * Get destination details (with 7-day cache) and attach saved status.
     */
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

        $destination['is_saved'] = in_array($placeId, $savedPlaceIds);

        return $destination;
    }

    /**
     * Fetch place details via SerpAPI Google Maps (type=place).
     * Returned shape is lighter — suitable for a quick detail card.
     */
    public function getDetails(string $placeId)
    {
        $place = $this->fetchPlaceFromSerpApi($placeId);

        return [
            'place_id'    => $placeId,
            'name'        => $place['title'] ?? '',
            'description' => $place['description'] ?? $place['address'] ?? '',
            'image'       => $place['images'][0]['image'] ?? $place['thumbnail'] ?? null,
            'rating'      => isset($place['rating'])
                ? (string) $place['rating']
                : '0',
            'website'     => $place['website'] ?? '',
            'latitude'    => isset($place['gps_coordinates']['latitude'])
                ? (string) $place['gps_coordinates']['latitude']
                : null,
            'longitude'   => isset($place['gps_coordinates']['longitude'])
                ? (string) $place['gps_coordinates']['longitude']
                : null,
            // Placeholder values for UI display
            'cost_of_living' => '',
            'visa'           => '',
            'food'           => '',
            'culture'        => '',
            'transport'      => '',
            'safety'         => '',
        ];
    }

    /**
     * Fetch full destination details via SerpAPI — used for the cached version.
     */
    public function getDestinationDetails(string $placeId)
    {
        $place = $this->fetchPlaceFromSerpApi($placeId);

        return [
            'place_id'    => $placeId,
            'name'        => $place['title'] ?? '',
            'description' => $place['description'] ?? $place['address'] ?? '',
            'image'       => $place['images'][0]['image'] ?? $place['thumbnail'] ?? null,
            'rating'      => isset($place['rating'])
                ? (string) $place['rating']
                : '0',
            'address'     => $place['address'] ?? '',
            'phone'       => $place['phone'] ?? '',
            'website'     => $place['website'] ?? '',
            'latitude'    => isset($place['gps_coordinates']['latitude'])
                ? (string) $place['gps_coordinates']['latitude']
                : null,
            'longitude'   => isset($place['gps_coordinates']['longitude'])
                ? (string) $place['gps_coordinates']['longitude']
                : null,
            // Placeholder values for UI display
            'cost_of_living' => '',
            'visa'           => '',
            'food'           => '',
            'culture'        => '',
            'transport'      => '',
            'safety'         => '',
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Call SerpAPI with engine=google_maps and type=place to retrieve a single
     * place's details by its place_id.
     *
     * @return array Raw place data from SerpAPI response
     */
    private function fetchPlaceFromSerpApi(string $placeId): array
    {
        try {
            $response = Http::get(self::SERPAPI_URL, [
                'engine'   => 'google_maps',
                'type'     => 'place',
                'place_id' => $placeId,
                'api_key'  => config('services.serpapi.key'),
            ])->json();

            return $response['place_results'] ?? [];
        } catch (\Throwable $e) {
            Log::error('SerpAPI place details error', [
                'place_id' => $placeId,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }
}
