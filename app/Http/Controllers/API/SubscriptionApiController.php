<?php

namespace App\Http\Controllers\API;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\RevenuecatEvent;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function Illuminate\Support\days;

class SubscriptionApiController extends Controller
{
    use ApiResponse;
    /**
     * RevenueCat Webhook
     */
    public function webhook(Request $request)
    {
        // dd("Ok");
        $expected = config('services.revenuecat.secret');

        // Log::info('RevenueCat authorization check', [
        //     'expected' => $expected,
        //     'authorization' => $request->header('Authorization'),
        // ]);


        // Log::info($request->all());
        // Log::info($expected);

        if ($request->header('Authorization') !== $expected) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }


        $event = $request->input('event');

        if (!$event) {
            return response()->json([
                'message' => 'Invalid payload'
            ], 422);
        }

        RevenuecatEvent::firstOrCreate(
            [
                'event_id' => $event['id']
            ],
            [
                'type' => $event['type'],
                'payload' => $request->all(),
            ]
        );

        $user = User::find($event['app_user_id']);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Always update the user's single subscription record.
        // type transitions: free → revenuecat on upgrade, revenuecat → free on downgrade.
        $subscription = Subscription::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'type' => 'revenuecat',
                'store' => $event['store'] ?? null,
                'product_id' => $event['product_id'] ?? null,
                'entitlement' => $event['entitlement_ids'][0] ?? null,
                'transaction_id' => $event['transaction_id'] ?? null,
                'original_transaction_id' => $event['original_transaction_id'] ?? null,
                'status' => match ($event['type']) {
                    'INITIAL_PURCHASE',
                    'RENEWAL',
                    'UNCANCELLATION',
                    'PRODUCT_CHANGE',
                    'SUBSCRIPTION_EXTENDED'
                    => 'active',
                    'CANCELLATION'
                    => 'cancelled',
                    'EXPIRATION'
                    => 'expired',
                    'BILLING_ISSUE'
                    => 'billing_issue',
                    'REFUND'
                    => 'refunded',
                    default
                    => 'active',
                },
                'purchase_date' => isset($event['purchased_at_ms']) ? Carbon::createFromTimestampMs($event['purchased_at_ms']) : null,
                'expires_at' => isset($event['expiration_at_ms']) ? Carbon::createFromTimestampMs($event['expiration_at_ms']) : null,
                'will_renew' => $event['auto_renew_status'] ?? false,
            ]
        );

        $user->updateUsageLimits();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Current subscription (free + RevenueCat)
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $freeSubscription = $user->subscriptions()
            ->where('type', 'free')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('free_expires_at')
                    ->orWhere('free_expires_at', '>', now());
            })
            ->first();

        $paidSubscription = $user->subscriptions()
            ->where('type', 'revenuecat')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        return response()->json([
            'is_subscribed' => $user->is_subscribed,
            'free_subscription' => $freeSubscription,
            'paid_subscription' => $paidSubscription,
            'has_used_free_trial' => $user->has_used_free_trial,
            'can_activate_free_trial' => $user->can_activate_free_trial,
            'eligible_for_free_trial' => $user->can_activate_free_trial && !$user->is_subscribed,
            'usage_stats' => $user->getUsageStats(),
            'tier' => $user->hasPaidSubscription() ? 'paid' : 'free',
        ]);
    }

    /**
     * Sync subscription
     */
    public function sync(Request $request)
    {
        return response()->json([
            'message' => 'Sync purchases from RevenueCat SDK on the client. The webhook will update the backend.'
        ]);
    }

    /**
     * Cancel paid subscription
     */
    public function cancel(Request $request)
    {
        $subscription = Subscription::where('user_id', $request->user()->id)
            ->where('type', 'revenuecat')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No paid subscription found.'
            ], 404);
        }

        $url = match ($subscription->store) {

            'APP_STORE'
            => 'https://apps.apple.com/account/subscriptions',

            'PLAY_STORE'
            => 'https://play.google.com/store/account/subscriptions',

            default
            => null,
        };

        return response()->json([
            'store' => $subscription->store,
            'manage_url' => $url,
        ]);
    }

    /**
     * Activate a free subscription (freemium).
     * Users can activate this at any time as long as can_activate_free_trial is true.
     */
    public function activateFree(Request $request)
    {
        $user = $request->user();

        if (!$user->can_activate_free_trial) {
            return $this->errorResponse('You have already activated a free subscription. Only one free trial per user.', 403);
        }

        $subscription = Subscription::firstOrCreate(
            ['user_id' => $user->id],
            [
                'type' => 'free',
                'store' => 'INTERNAL',
                'product_id' => 'free',
                'entitlement' => 'free',
                'status' => 'active',
                'purchase_date' => now(),
                'expires_at' => now()->addDays(7),
                'free_expires_at' => now()->addDays(7),
                'trial_type' => 'free',
                'will_renew' => false,
            ]
        );

        // Mark as no longer eligible for free subscription
        $user->update(['can_activate_free_trial' => false]);

        $user->updateUsageLimits();

        return response()->json([
            'success' => true,
            'subscription' => $subscription->load('user'),
        ]);
    }

    /**
     * Activate a free trial.
     * Only users who haven't used a trial before can activate.
     */
    public function activateFreeTrial(Request $request)
    {
        $user = $request->user();

        // Check if user already used a free trial
        if (!$user->can_activate_free_trial) {
            return $this->errorResponse('You have already activated a free subscription. Only one free trial per user.', 403);
        }

        // Check if user already has an active subscription (paid or free)
        if ($user->is_subscribed) {
            return response()->json([
                'message' => 'You already have an active subscription.',
                'success' => false,
            ], 400);
        }

        // Default trial duration: 7 days (can be configurable)
        $trialDuration = config('subscription.free_trial_days', 7);

        $subscription = Subscription::firstOrCreate(
            ['user_id' => $user->id],
            [
                'type' => 'free',
                'store' => 'INTERNAL',
                'product_id' => 'free_trial',
                'entitlement' => 'free_trial',
                'status' => 'active',
                'purchase_date' => now(),
                'expires_at' => null,
                'free_expires_at' => now()->addDays($trialDuration),
                'trial_type' => 'free_trial',
                'will_renew' => false,
            ]
        );

        // Mark as no longer eligible for free subscription
        $user->update(['can_activate_free_trial' => false]);

        $user->updateUsageLimits();

        return response()->json([
            'success' => true,
            'subscription' => $subscription->load('user'),
            'trial_duration_days' => $trialDuration,
            'expires_at' => $subscription->free_expires_at,
        ]);
    }

    /**
     * Check free trial eligibility and status.
     */
    public function checkTrial(Request $request)
    {
        $user = $request->user();

        $hasFreeTrial = $user->hasUsedFreeTrial();
        $activeFree = $user->active_free_subscription;
        $canActivate = $user->can_activate_free_trial;

        // Check if free trial expired
        $trialExpired = false;
        if ($hasFreeTrial && !$activeFree) {
            $trialExpired = true;
        }

        return response()->json([
            'eligible' => $canActivate && !$user->is_subscribed,
            'has_used_trial' => $hasFreeTrial,
            'trial_expired' => $trialExpired,
            'active_free_subscription' => $activeFree ? $activeFree : null,
            'can_activate_free_trial' => $canActivate,
        ]);
    }

    /**
     * Extend free trial (admin only).
     */
    public function extendFreeTrial(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'days' => 'required|integer|min:1|max:365',
        ]);

        $user = User::findOrFail($request->user_id);
        $days = $request->days;

        $subscription = $user->subscriptions()
            ->where('type', 'free')
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            // Create a new free trial subscription
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'type' => 'free',
                'store' => 'INTERNAL',
                'product_id' => 'free_trial',
                'entitlement' => 'free_trial',
                'status' => 'active',
                'purchase_date' => now(),
                'expires_at' => null,
                'free_expires_at' => now()->addDays($days),
                'trial_type' => 'free_trial',
                'will_renew' => false,
            ]);
        } else {
            // Extend existing free subscription
            $currentExpiry = $subscription->free_expires_at ?? now();
            $subscription->update([
                'free_expires_at' => $currentExpiry->addDays($days),
            ]);
        }

        $user->updateUsageLimits();

        return response()->json([
            'success' => true,
            'subscription' => $subscription->load('user'),
            'extended_until' => $subscription->free_expires_at,
        ]);
    }

    /**
     * Deactivate free subscription (only free can deactivate free).
     */
    public function deactivateFree(Request $request)
    {
        $subscription = $request->user()->subscriptions()
            ->where('type', 'free')
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No active free subscription found.',
            ], 404);
        }

        $subscription->update(['status' => 'cancelled', 'free_expires_at' => now()]);

        $request->user()->updateUsageLimits();

        return response()->json([
            'success' => true,
            'message' => 'Free subscription deactivated.',
        ]);
    }

    /**
     * Get detailed usage statistics for the authenticated user.
     */
    public function usageStats(Request $request)
    {
        $user = $request->user();

        // dd($user->getActiveSubscriptionAttribute());

        return $this->successResponse('Usage Statstics', [
            'tier' => $user->hasPaidSubscription() ? 'paid' : 'free',
            'limits' => $user->getTierLimits(),
            'usage' => $user->getUsageStats(),
            'subscription' => $user->getActiveSubscriptionAttribute(),
        ]);
    }

    /**
     * Force upgrade user to paid tier (API - admin only).
     */
    public function forceUpgrade(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);

        Subscription::updateOrCreate(
            ['user_id' => $userId],
            [
                'type' => 'revenuecat',
                'store' => 'INTERNAL',
                'product_id' => 'paid_override',
                'entitlement' => 'paid',
                'status' => 'active',
                'purchase_date' => now(),
                'expires_at' => now()->addYear(),
                'will_renew' => false,
            ]
        );

        $user->updateUsageLimits();

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} upgraded to paid tier.",
        ]);
    }

    /**
     * Force downgrade user to free tier (API - admin only).
     */
    public function forceDowngrade(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);

        Subscription::where('user_id', $userId)
            ->where('type', 'revenuecat')
            ->update(['status' => 'cancelled']);

        $user->updateUsageLimits();

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} downgraded to free tier.",
        ]);
    }
}
