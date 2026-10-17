<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chat>
 */
class ChatFactory extends Factory
{
    protected $model = Chat::class;

    public function definition(): array
    {
        $groupNames = [
            'Tokyo Foodies & Explorers 🍜',
            'Lisbon Remote Workers Hub 💻',
            'Backpackers Europe Summer 2026 🎒',
            'Bali Digital Nomads & Surfers 🌴',
            'Rome & Florence Photography Walk 📸',
        ];

        $groupImages = [
            'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=300&q=80',
            'https://images.unsplash.com/photo-1513581166391-887a96ddeafd?auto=format&fit=crop&w=300&q=80',
            'https://images.unsplash.com/photo-1527631746610-bca00a040d60?auto=format&fit=crop&w=300&q=80',
        ];

        return [
            'name' => fake()->randomElement($groupNames),
            'image' => fake()->randomElement($groupImages),
            'is_group' => true,
            'created_by' => User::factory(),
        ];
    }

    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => null,
            'image' => null,
            'is_group' => false,
        ]);
    }
}
