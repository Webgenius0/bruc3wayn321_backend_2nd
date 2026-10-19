<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\UsageLimit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $currentMonth = now()->format('Y-m-01');

        foreach ($users as $index => $user) {
            // Determine tier based on email or alternating index
            if (in_array($user->email, ['superadmin@gmail.com', 'admin@gmail.com', 'user@gmail.com', 'sarah@example.com'])) {
                // Paid RevenueCat subscribers
                Subscription::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'store' => 'app_store',
                        'type' => 'revenuecat',
                        'product_id' => 'rc_premium_yearly',
                        'entitlement' => 'premium_access',
                        'transaction_id' => 'GPA.' . fake()->numerify('####-####-####-#####'),
                        'original_transaction_id' => 'GPA.' . fake()->numerify('####-####-####-#####'),
                        'status' => 'active',
                        'purchase_date' => now()->subMonths(2),
                        'expires_at' => now()->addMonths(10),
                        'free_expires_at' => null,
                        'trial_type' => null,
                        'will_renew' => true,
                    ]
                );

                // Paid tier limits
                $limits = config('subscription.tiers.paid', [
                    'ai_chat_messages' => 1000,
                    'ai_voice_requests' => 200,
                    'community_posts' => 99999,
                ]);

                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'ai_chat', 'period_start' => $currentMonth],
                    ['limit' => $limits['ai_chat_messages'], 'current_count' => fake()->numberBetween(15, 120)]
                );
                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'ai_voice', 'period_start' => $currentMonth],
                    ['limit' => $limits['ai_voice_requests'], 'current_count' => fake()->numberBetween(5, 30)]
                );
                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'posts', 'period_start' => $currentMonth],
                    ['limit' => $limits['community_posts'], 'current_count' => fake()->numberBetween(2, 8)]
                );
            } elseif ($index % 3 === 0) {
                // Free Trial subscribers (7-day trial active)
                Subscription::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'store' => null,
                        'type' => 'free',
                        'product_id' => 'free_trial_tier',
                        'entitlement' => 'trial_access',
                        'transaction_id' => 'trial_' . Str::random(10),
                        'original_transaction_id' => null,
                        'status' => 'active',
                        'purchase_date' => now()->subDays(2),
                        'expires_at' => null,
                        'free_expires_at' => now()->addDays(5),
                        'trial_type' => 'free_trial',
                        'will_renew' => false,
                    ]
                );

                $limits = config('subscription.tiers.free', [
                    'ai_chat_messages' => 50,
                    'ai_voice_requests' => 50,
                    'community_posts' => 5,
                ]);

                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'ai_chat', 'period_start' => $currentMonth],
                    ['limit' => $limits['ai_chat_messages'], 'current_count' => fake()->numberBetween(5, 25)]
                );
                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'ai_voice', 'period_start' => $currentMonth],
                    ['limit' => $limits['ai_voice_requests'], 'current_count' => fake()->numberBetween(1, 10)]
                );
                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'posts', 'period_start' => $currentMonth],
                    ['limit' => $limits['community_posts'], 'current_count' => fake()->numberBetween(1, 3)]
                );
            } else {
                // Freemium users
                Subscription::updateOrCreate(
                    ['user_id' => $user->id],
                    [
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
                    ]
                );

                $limits = config('subscription.tiers.free', [
                    'ai_chat_messages' => 50,
                    'ai_voice_requests' => 50,
                    'community_posts' => 5,
                ]);

                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'ai_chat', 'period_start' => $currentMonth],
                    ['limit' => $limits['ai_chat_messages'], 'current_count' => fake()->numberBetween(0, 48)]
                );
                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'ai_voice', 'period_start' => $currentMonth],
                    ['limit' => $limits['ai_voice_requests'], 'current_count' => fake()->numberBetween(0, 15)]
                );
                UsageLimit::updateOrCreate(
                    ['user_id' => $user->id, 'type' => 'posts', 'period_start' => $currentMonth],
                    ['limit' => $limits['community_posts'], 'current_count' => fake()->numberBetween(0, 4)]
                );
            }
        }
    }
}
