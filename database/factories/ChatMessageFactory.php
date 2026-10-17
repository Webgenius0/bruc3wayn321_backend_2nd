<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    public function definition(): array
    {
        $messages = [
            'Hey everyone! Who is in Tokyo this weekend? Looking to grab ramen around Shibuya!',
            'I just arrived yesterday! Staying in Shinjuku. Count me in for dinner!',
            'Does anyone know if we need advance reservations for teamLab Planets?',
            'Yes! Definitely book at least a week ahead on their official website.',
            'Working from a great cafe in Canggu right now, fast WiFi and awesome iced matcha.',
            'Can anyone recommend a good coworking space in Lisbon near Principe Real?',
            'Second Home Lisbon inside the Mercado da Ribeira is fantastic, lots of natural light and plants.',
            'The weather in Rome is gorgeous today, perfect for walking around the Colosseum.',
        ];

        return [
            'chat_id' => Chat::factory(),
            'sender_id' => User::factory(),
            'message' => fake()->randomElement($messages),
            'links' => null,
            'type' => 'text',
            'is_read' => fake()->boolean(80),
        ];
    }
}
