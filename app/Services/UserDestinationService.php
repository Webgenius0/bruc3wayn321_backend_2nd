<?php

namespace App\Services;

use App\Repositories\UserDestinationRepository;

class UserDestinationService
{
    public function __construct(
        protected UserDestinationRepository $repository,
        protected DestinationService $destinationService
    ) {}

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

                return $this->destinationService
                    ->details($destination->place_id);
            });
    }
}
