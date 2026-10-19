<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_free_trial_returns_is_subscribed_false_and_free_tier()
    {
        $user = User::factory()->create([
            'can_activate_free_trial' => false,
        ]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'type' => 'free',
            'store' => 'INTERNAL',
            'product_id' => 'free_trial',
            'entitlement' => 'free_trial',
            'status' => 'active',
            'purchase_date' => now()->subDays(14),
            'expires_at' => null,
            'free_expires_at' => now()->subDays(7),
            'trial_type' => 'free_trial',
            'will_renew' => true,
        ]);

        // Prior to sync or after query, activeSubscription must be null
        $this->assertNull($user->activeSubscription);
        $this->assertFalse($user->is_subscribed);
        $this->assertSame('free', $user->current_subscription);
        $this->assertTrue($user->has_used_free_trial);

        // Run syncExpiredSubscriptions
        $user->syncExpiredSubscriptions();

        $sub->refresh();
        $this->assertSame('expired', $sub->status);
        $this->assertFalse($sub->will_renew);

        // API show endpoint check
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/subscription');

        $response->assertStatus(200)
            ->assertJson([
                'is_subscribed' => false,
                'free_subscription' => null,
                'paid_subscription' => null,
                'has_used_free_trial' => true,
                'can_activate_free_trial' => false,
                'tier' => 'free',
            ]);

        // API check trial endpoint check
        $trialResponse = $this->getJson('/api/v1/subscription/trial-status');
        $trialResponse->assertStatus(200)
            ->assertJson([
                'eligible' => false,
                'has_used_trial' => true,
                'trial_expired' => true,
                'active_free_subscription' => null,
            ]);
    }

    public function test_active_free_trial_returns_is_subscribed_true_and_free_tier()
    {
        $user = User::factory()->create([
            'can_activate_free_trial' => false,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'type' => 'free',
            'store' => 'INTERNAL',
            'product_id' => 'free_trial',
            'entitlement' => 'free_trial',
            'status' => 'active',
            'purchase_date' => now()->subDays(2),
            'expires_at' => null,
            'free_expires_at' => now()->addDays(5),
            'trial_type' => 'free_trial',
            'will_renew' => false,
        ]);

        $this->assertNotNull($user->activeSubscription);
        $this->assertTrue($user->is_subscribed);
        $this->assertSame('free', $user->current_subscription);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/subscription');

        $response->assertStatus(200)
            ->assertJson([
                'is_subscribed' => true,
                'has_used_free_trial' => true,
                'tier' => 'free',
            ]);
        $this->assertNotNull($response->json('free_subscription'));
    }

    public function test_active_paid_subscription_returns_is_subscribed_true_and_paid_tier()
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'type' => 'revenuecat',
            'store' => 'APP_STORE',
            'product_id' => 'annual_pro',
            'entitlement' => 'pro',
            'status' => 'active',
            'purchase_date' => now()->subDays(30),
            'expires_at' => now()->addMonths(11),
            'free_expires_at' => null,
            'will_renew' => true,
        ]);

        $this->assertTrue($user->is_subscribed);
        $this->assertSame('paid', $user->current_subscription);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/subscription');

        $response->assertStatus(200)
            ->assertJson([
                'is_subscribed' => true,
                'tier' => 'paid',
            ]);
        $this->assertNotNull($response->json('paid_subscription'));
    }

    public function test_expired_paid_subscription_syncs_to_expired_and_free_tier()
    {
        $user = User::factory()->create();

        $sub = Subscription::create([
            'user_id' => $user->id,
            'type' => 'revenuecat',
            'store' => 'APP_STORE',
            'product_id' => 'annual_pro',
            'entitlement' => 'pro',
            'status' => 'active',
            'purchase_date' => now()->subYear(),
            'expires_at' => now()->subDays(1),
            'free_expires_at' => null,
            'will_renew' => true,
        ]);

        $user->syncExpiredSubscriptions();

        $sub->refresh();
        $this->assertSame('expired', $sub->status);
        $this->assertFalse($sub->will_renew);
        $this->assertFalse($user->is_subscribed);
        $this->assertSame('free', $user->current_subscription);
    }

    public function test_subscription_expire_overdue_static_method()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Overdue free
        $sub1 = Subscription::create([
            'user_id' => $user1->id,
            'type' => 'free',
            'product_id' => 'free',
            'status' => 'active',
            'free_expires_at' => now()->subDay(),
            'will_renew' => true,
        ]);

        // Overdue paid
        $sub2 = Subscription::create([
            'user_id' => $user2->id,
            'type' => 'revenuecat',
            'product_id' => 'pro_annual',
            'status' => 'active',
            'expires_at' => now()->subHour(),
            'will_renew' => true,
        ]);

        $count = Subscription::expireOverdue();
        $this->assertEquals(2, $count);

        $this->assertSame('expired', $sub1->fresh()->status);
        $this->assertFalse($sub1->fresh()->will_renew);
        $this->assertSame('expired', $sub2->fresh()->status);
        $this->assertFalse($sub2->fresh()->will_renew);
    }

    public function test_activate_free_trial_endpoint_flow()
    {
        $user = User::factory()->create([
            'can_activate_free_trial' => true,
        ]);

        Sanctum::actingAs($user);

        // Before trial activation
        $this->assertFalse($user->is_subscribed);
        $this->assertFalse($user->has_used_free_trial);
        $this->assertTrue($user->can_activate_free_trial);

        $response = $this->postJson('/api/v1/subscription/activate-trial');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'trial_duration_days' => 7,
            ]);

        $user->refresh();
        $this->assertTrue($user->is_subscribed);
        $this->assertFalse($user->can_activate_free_trial);
        $this->assertTrue($user->has_used_free_trial);

        $sub = $user->subscriptions()->first();
        $this->assertSame('free', $sub->type);
        $this->assertSame('active', $sub->status);
        $this->assertFalse($sub->will_renew);
        $this->assertNotNull($sub->free_expires_at);

        // Cannot activate second time
        $secondResponse = $this->postJson('/api/v1/subscription/activate-trial');
        $secondResponse->assertStatus(403);
    }

    public function test_expired_trial_user_is_blocked_by_subscription_middleware()
    {
        $user = User::factory()->create([
            'can_activate_free_trial' => false,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'type' => 'free',
            'product_id' => 'free_trial',
            'status' => 'active',
            'purchase_date' => now()->subDays(10),
            'free_expires_at' => now()->subDays(3),
            'will_renew' => false,
        ]);

        Sanctum::actingAs($user);

        // Call an endpoint protected by subscription:any middleware
        $response = $this->postJson('/api/v1/ai-concierge/chat', [
            'message' => 'Hello',
        ]);

        $response->assertStatus(403);
    }

    public function test_api_test_expire_endpoint()
    {
        $user = User::factory()->create([
            'can_activate_free_trial' => false,
        ]);

        $sub = Subscription::create([
            'user_id' => $user->id,
            'type' => 'free',
            'product_id' => 'free_trial',
            'status' => 'active',
            'free_expires_at' => now()->addDays(7),
            'will_renew' => true,
        ]);

        $this->assertTrue($user->is_subscribed);

        // Hit the test-expire endpoint with user ID in URL
        $response = $this->postJson("/api/v1/subscription/test-expire/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'database_before_status_call' => [
                    'status_in_db' => 'active',
                    'will_renew_in_db' => true,
                ],
                'immediate_check' => [
                    'is_subscribed' => false,
                    'current_subscription' => 'free',
                ],
            ]);

        // Verify that in the DB before GET /subscription, status is still 'active' and will_renew is still true
        $this->assertSame('active', $sub->fresh()->status);
        $this->assertTrue($sub->fresh()->will_renew);

        // But evaluated is_subscribed is ALREADY false!
        $user->refresh();
        $this->assertFalse($user->is_subscribed);

        // Now simulate the frontend calling GET /api/v1/subscription
        Sanctum::actingAs($user);
        $showResponse = $this->getJson('/api/v1/subscription');
        $showResponse->assertStatus(200)
            ->assertJson([
                'is_subscribed' => false,
                'free_subscription' => null,
                'tier' => 'free',
            ]);

        // After frontend called the API, DB status has automatically synced to expired and will_renew to false
        $this->assertSame('expired', $sub->fresh()->status);
        $this->assertFalse($sub->fresh()->will_renew);

        // Hit the test-reset-trial endpoint to restore it
        $resetResponse = $this->postJson("/api/v1/subscription/test-reset-trial/{$user->id}");

        $resetResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'is_subscribed' => true,
                    'current_subscription' => 'free',
                ],
            ]);

        $user->refresh();
        $this->assertTrue($user->is_subscribed);
        $this->assertSame('active', $sub->fresh()->status);
    }
}


