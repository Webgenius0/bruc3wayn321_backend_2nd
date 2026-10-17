<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserReport>
 */
class UserReportFactory extends Factory
{
    protected $model = UserReport::class;

    public function definition(): array
    {
        $descriptions = [
            'User is repeatedly spamming unsolicited tour agency affiliate links in direct messages.',
            'Posting deceptive accommodation reviews with misleading photos.',
            'Aggressive behavior and harassment in the group travel discussion.',
            'Suspected commercial travel agent operating under a personal profile without verification.',
        ];

        return [
            'reporter_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'description' => fake()->randomElement($descriptions),
            'status' => fake()->randomElement(['pending', 'reviewed', 'resolved']),
            'admin_note' => 'Reviewed user profile and recent activity log.',
            'reviewed_at' => now()->subDays(fake()->numberBetween(1, 5)),
            'reviewer_id' => 1,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'admin_note' => null,
            'reviewed_at' => null,
            'reviewer_id' => null,
        ]);
    }
}
