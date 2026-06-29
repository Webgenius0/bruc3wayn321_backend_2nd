<?php

namespace App\Services;

use App\Models\UserDestination;
use App\Repositories\UserDestinationRepository;
use App\Services\DestinationService;

class UserDestinationService
{
    public function __construct(
        protected UserDestinationRepository $repository,
        protected DestinationService $destinationService
    ) {}

    public function toggle(string $placeId): bool
    {
        $destination = UserDestination::where('user_id', auth()->id())
            ->where('place_id', $placeId)
            ->first();

        if ($destination) {
            $destination->delete();

            return false;
        }

        UserDestination::create([
            'user_id' => auth()->id(),
            'place_id' => $placeId,
        ]);

        return true;
    }

    public function save(string $placeId)
    {
        return $this->repository->save(
            auth()->id(),
            $placeId
        );
    }

    public function remove(string $placeId)
    {
        return $this->repository->remove(
            auth()->id(),
            $placeId
        );
    }

    public function myDestinations($id)
    {
        return $this->repository
            ->myDestinations($id)
            ->map(function ($destination) {

                $place = $this->destinationService->details($destination->place_id);

                // dd($place);

                return [
                    'place_id'   => $place['place_id'] ?? '',
                    'title'      => $place['name'] ?? '',
                    'subtitle'   => $place['description'] ?? '',
                    'description' => $place['description'] ?? '',
                    'rating'     => isset($place['rating'])
                        ? (string) $place['rating']
                        : '0',
                    'image'      => $place['image'],
                    'latitude'   => isset($place['latitude'])
                        ? (string) $place['latitude']
                        : null,
                    'longitude'  => isset($place['longitude'])
                        ? (string) $place['longitude']
                        : null,
                    'is_saved'    => UserDestination::where('user_id', auth()->id())
                        ->where('place_id', $place['place_id'])
                        ->exists(),
                ];
            })
            ->values();
    }
}
