<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDestination;
use Illuminate\Database\Seeder;

class UserDestinationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        // Real Google Place IDs for prominent global destinations
        $placeIds = [
            'ChIJ2V-Mo_l1nkcRfZixfUq4DAE', // Paris, France
            'ChIJ513GhMSgAWARi3WmzpRgahQ', // Tokyo, Japan
            'ChIJbU60Qlu4j4ARbvSm5Mr0viF', // San Francisco, USA
            'ChIJOwg_06VPwokRYv534QaPC8g', // New York, USA
            'ChIJL_P_CXMEDTkRw0ZiMnKKQEw', // Bali, Indonesia
            'ChIJpTvG16DL1IkRdNrjt079hhw', // Rome, Italy
            'ChIJD7fiBh9u5kcRYJSMaMOCCwQ', // Barcelona, Spain
            'ChIJdUa2BhV0AWARxQNImT5OPQ8', // Kyoto, Japan
            'ChIJdedCxVOikWsRAFDoBqA0Blc', // Singapore
            'ChIJ09iA7p_91EcRkXw_69M49s8', // Lisbon, Portugal
            'ChIJ9W65JW8210ARn6vB68p4zC8', // Dubrovnik, Croatia
            'ChIJL688kghuEmsRmgw4aa-0AA0', // Sydney, Australia
            'ChIJ8TuhsOZ9AhwRhx3v4yM6tXk', // Bangkok, Thailand
            'ChIJk4959z1LzBwRXnI700_v1-4', // Cape Town, South Africa
        ];

        foreach ($users as $user) {
            // Assign 3 to 6 saved destinations per user
            $selectedPlaces = collect($placeIds)->random(fake()->numberBetween(3, 6));
            foreach ($selectedPlaces as $placeId) {
                UserDestination::firstOrCreate([
                    'user_id' => $user->id,
                    'place_id' => $placeId,
                ]);
            }
        }
    }
}
