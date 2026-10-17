<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDestination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDestination>
 */
class UserDestinationFactory extends Factory
{
    protected $model = UserDestination::class;

    public function definition(): array
    {
        $popularPlaceIds = [
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

        return [
            'user_id' => User::factory(),
            'place_id' => fake()->randomElement($popularPlaceIds),
        ];
    }
}
