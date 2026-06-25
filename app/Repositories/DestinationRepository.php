<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;

class DestinationRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getNearbyHotels(float $lat, float $lng, int $radius = 5000)
    {
        $response = Http::get(
            'https://maps.googleapis.com/maps/api/place/textsearch/json',
            [
                'query'    => 'hotels',
                'location' => "{$lat},{$lng}",
                'radius'   => $radius,
                'key'      => config('services.google.places_api_key'),
            ]
        );

        return $response->json();
    }
}
