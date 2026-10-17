<?php

namespace Database\Factories;

use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiMessage>
 */
class AiMessageFactory extends Factory
{
    protected $model = AiMessage::class;

    public function definition(): array
    {
        return [
            'ai_conversation_id' => AiConversation::factory(),
            'role' => 'user',
            'content' => 'What are the top three things I should not miss in Tokyo?',
        ];
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
            'content' => "Here are three unforgettable experiences in Tokyo:\n1. Early morning stroll through Asakusa and Senso-ji temple.\n2. Sunset panoramic view from Shibuya Sky observatory.\n3. Exploring the tiny yakitori stalls in Omoide Yokocho (Memory Lane), Shinjuku.",
        ]);
    }
}
