<?php

namespace App\Repositories;

use App\Models\UserDestination;

class UserDestinationRepository
{
    /**
     * Create a new class instance.
     */
    public function save(int $userId, string $placeId)
    {
        return UserDestination::firstOrCreate([
            'user_id' => $userId,
            'place_id' => $placeId,
        ]);
    }

    public function remove(int $userId, string $placeId)
    {
        return UserDestination::where([
            'user_id' => $userId,
            'place_id' => $placeId,
        ])->delete();
    }

    public function myDestinations(int $userId)
    {
        return UserDestination::where(
            'user_id',
            $userId
        )->latest()->get();
    }

    public function exists(int $userId, string $placeId): bool
    {
        return UserDestination::where([
            'user_id' => $userId,
            'place_id' => $placeId,
        ])->exists();
    }
}
