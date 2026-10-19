<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store' => fake()->randomElement(['app_store', 'play_store']),
            'type' => 'revenuecat',
            'product_id' => fake()->randomElement(['rc_premium_monthly', 'rc_premium_yearly']),
            'entitlement' => 'premium_access',
            'transaction_id' => 'GPA.' . fake()->numerify('####-####-####-#####'),
            'original_transaction_id' => 'GPA.' . fake()->numerify('####-####-####-#####'),
            'status' => 'active',
            'purchase_date' => now()->subMonths(fake()->numberBetween(1, 6)),
            'expires_at' => now()->addMonths(fake()->numberBetween(1, 12)),
            'free_expires_at' => null,
            'trial_type' => null,
            'will_renew' => true,
        ];
    }

    public function freeTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'store' => null,
            'type' => 'free',
            'product_id' => 'free_trial_tier',
            'entitlement' => 'trial_access',
            'transaction_id' => 'trial_' . Str::random(12),
            'original_transaction_id' => null,
            'status' => 'active',
            'purchase_date' => now()->subDays(2),
            'expires_at' => null,
            'free_expires_at' => now()->addDays(5),
            'trial_type' => 'free_trial',
            'will_renew' => false,
        ]);
    }

    public function freemium(): static
    {
        return $this->state(fn (array $attributes) => [
            'store' => null,
            'type' => 'free',
            'product_id' => 'freemium_tier',
            'entitlement' => 'basic_access',
            'transaction_id' => null,
            'original_transaction_id' => null,
            'status' => 'active',
            'purchase_date' => now()->subMonths(1),
            'expires_at' => null,
            'free_expires_at' => null,
            'trial_type' => 'freemium',
            'will_renew' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subDays(10),
            'free_expires_at' => now()->subDays(10),
            'will_renew' => false,
        ]);
    }
}
