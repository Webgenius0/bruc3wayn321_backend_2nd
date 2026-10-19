<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostComment>
 */
class PostCommentFactory extends Factory
{
    protected $model = PostComment::class;

    public function definition(): array
    {
        $comments = [
            'What a gorgeous view! Definitely putting this on my itinerary for next month 🌟',
            'Did you need an international driving permit to rent a scooter there?',
            'The food looks incredible! What was the name of the restaurant?',
            'How many days would you recommend spending in this area?',
            'Stunning photography! What camera and lens did you use for this shot?',
            'I was there last summer! Did you get a chance to check out the hidden beach nearby?',
            'Such helpful travel tips! Thanks for sharing this gem 🙌',
            'Is public transit easy to navigate there or do you recommend ride-hailing apps?',
        ];

        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->randomElement($comments),
        ];
    }
}
