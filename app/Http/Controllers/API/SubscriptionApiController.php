<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RevenuecatEvent;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionApiController extends Controller
{
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
        $isFreePlan = in_array($event['type'], [
            'EXPIRATION',
            'REFUND',
        ]);

        Subscription::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
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

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Current subscription
     */
    public function show(Request $request)
    {
        return response()->json(
            Subscription::where('user_id', $request->user()->id)->first()
        );
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
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $subscription = Subscription::where('user_id', $request->user()->id)->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No subscription found.'
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
}
