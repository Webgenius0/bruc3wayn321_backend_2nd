<?php

namespace Database\Factories;

use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiConversation>
 */
class AiConversationFactory extends Factory
{
    protected $model = AiConversation::class;

    public function definition(): array
    {
        $titles = [
            '7-Day Tokyo & Kyoto Culinary Itinerary',
            'Relocating to Portugal: D8 Digital Nomad Visa Guide',
            'Best Neighborhoods in Lisbon for Remote Work',
            'Solo Female Travel in Southeast Asia: Safety & Routes',
            '3-Day Budget Weekend in Rome and Florence',
            'Cost of Living Comparison: Bali vs Chiang Mai',
            'Hiking the Swiss Alps: Interlaken to Grindelwald',
            'Packing Essentials for 3 Months Backpacking in Europe',
        ];

        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement($titles),
        ];
    }
}
