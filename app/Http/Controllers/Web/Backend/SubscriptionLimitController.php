<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\UsageLimit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionLimitController extends Controller
{
    /**
     * Display subscription limit settings.
     */
    public function index(Request $request): Response
    {
        $tiers = Config::get('subscription.tiers');

        $search = $request->input('search');
        $tier = $request->input('tier');
        $perPage = $request->input('per_page', 15);

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        if ($tier) {
            $query->where(function ($q) use ($tier) {
                if ($tier === 'paid') {
                    $q->whereHas('subscriptions', function ($subQ) {
                        $subQ->where('type', 'revenuecat')
                            ->where('status', 'active')
                            ->where(function ($inner) {
                                $inner->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', now());
                            });
                    });
                } else {
                    $q->where(function ($subQ) {
                        $subQ->whereDoesntHave('subscriptions', function ($paidQ) {
                            $paidQ->where('type', 'revenuecat')
                                ->where('status', 'active')
                                ->where(function ($inner) {
                                    $inner->whereNull('expires_at')
                                        ->orWhere('expires_at', '>', now());
                                });
                        });
                    });
                }
            });
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Build URL-based pagination links (no HTML) for Inertia
        $paginationLinks = [];
        if ($paginator->hasPages()) {
            // Query params to preserve across pages
            $queryParams = array_filter([
                'search' => $search ?: null,
                'tier' => $tier ?: null,
            ]);
            $queryParamsStr = '';
            if (!empty($queryParams)) {
                $queryParamsStr = '&' . http_build_query($queryParams);
            }

            for ($page = 1; $page <= $paginator->lastPage(); $page++) {
                if ($page === 1) {
                    $url = '/admin/subscription-limits';
                    if ($queryParamsStr !== '') {
                        $url .= '?' . substr($queryParamsStr, 1); // remove leading &
                    }
                } else {
                    $url = "/admin/subscription-limits?page={$page}{$queryParamsStr}";
                }
                $paginationLinks[] = [
                    'url' => $url,
                    'label' => (string) $page,
                    'active' => $page === $paginator->currentPage(),
                ];
            }
            // prev
            $prevPage = $paginator->currentPage() - 1;
            array_unshift($paginationLinks, [
                'url' => $paginator->currentPage() > 1 ? "/admin/subscription-limits?page={$prevPage}{$queryParamsStr}" : null,
                'label' => '« Previous',
                'active' => false,
            ]);
            // next
            $nextPage = $paginator->currentPage() + 1;
            $paginationLinks[] = [
                'url' => $paginator->currentPage() < $paginator->lastPage() ? "/admin/subscription-limits?page={$nextPage}{$queryParamsStr}" : null,
                'label' => 'Next »',
                'active' => false,
            ];
        }

        $userUsageData = $paginator->getCollection()->map(function ($user) {
            $stats = $user->getUsageStats();
            $subscription = $user->active_subscription;

            return [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'tier' => $user->hasPaidSubscription() ? 'paid' : 'free',
                'subscription' => $subscription ? [
                    'status' => $subscription->status,
                    'type' => $subscription->type,
                    'expires_at' => $subscription->expires_at,
                    'free_expires_at' => $subscription->free_expires_at,
                ] : null,
                'usage' => $stats,
            ];
        })->values();

        return Inertia::render('backend/subscription-limits/index', [
            'tiers' => $tiers,
            'freeTrialDays' => Config::get('subscription.free_trial_days', 7),
            'freeTrialEnabled' => Config::get('subscription.free_trial_enabled', true),
            'userUsageData' => $userUsageData,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'links' => $paginationLinks,
            ],
            'search' => $search,
            'tierFilter' => $tier,
        ]);
    }


    /**
     * Update tier limits.
     */
    public function updateLimits(Request $request)
    {
        $request->validate([
            'free_ai_chat_messages' => 'required|integer|min:0',
            'free_ai_voice_requests' => 'required|integer|min:0',
            'free_community_posts' => 'required|integer|min:0',
            'paid_ai_chat_messages' => 'required|integer|min:0',
            'paid_ai_voice_requests' => 'required|integer|min:0',
            'paid_community_posts' => 'required|integer|min:0',
        ]);

        Config::set('subscription.tiers', [
            'free' => [
                'ai_chat_messages' => (int) $request->free_ai_chat_messages,
                'ai_voice_requests' => (int) $request->free_ai_voice_requests,
                'community_posts' => (int) $request->free_community_posts,
            ],
            'paid' => [
                'ai_chat_messages' => (int) $request->paid_ai_chat_messages,
                'ai_voice_requests' => (int) $request->paid_ai_voice_requests,
                'community_posts' => (int) $request->paid_community_posts,
            ],
        ]);

        // Persist to config file
        $configPath = config_path('subscription.php');
        $configContent = "<?php\n\nreturn [\n    'free_trial_days' => " . Config::get('subscription.free_trial_days') . ",\n    'free_trial_enabled' => " . (Config::get('subscription.free_trial_enabled') ? 'true' : 'false') . ",\n    'freemium_enabled' => true,\n    'tiers' => [\n        'free' => [\n            'ai_chat_messages' => " . (int) $request->free_ai_chat_messages . ",\n            'ai_voice_requests' => " . (int) $request->free_ai_voice_requests . ",\n            'community_posts' => " . (int) $request->free_community_posts . ",\n        ],\n        'paid' => [\n            'ai_chat_messages' => " . (int) $request->paid_ai_chat_messages . ",\n            'ai_voice_requests' => " . (int) $request->paid_ai_voice_requests . ",\n            'community_posts' => " . (int) $request->paid_community_posts . ",\n        ],\n    ],\n];";
        file_put_contents($configPath, $configContent);

        return redirect()->back()->with('success', 'Subscription limits updated successfully.');
    }


    /**
     * Alias for index — keeps the /users route working.
     */
    public function userUsage(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Reset a user's usage count for a specific type.
     */
    public function resetUsage(Request $request, int $userId)
    {
        $request->validate([
            'type' => 'required|in:ai_chat,ai_voice,posts',
        ]);

        $user = User::findOrFail($userId);
        $user->decrementUsage($request->type, 99999); // Reset to 0

        return back()->with('success', "Usage for {$request->type} reset for {$user->name}.");
    }

    /**
     * Force upgrade user to paid tier (for admin override).
     */
    public function forceUpgrade(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);

        // Create a paid subscription record
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

        return back()->with('success', "User {$user->name} upgraded to paid tier.");
    }

    /**
     * Downgrade user to free tier (for admin override).
     */
    public function forceDowngrade(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);

        // Remove paid subscription
        Subscription::where('user_id', $userId)
            ->where('type', 'revenuecat')
            ->update(['status' => 'cancelled']);

        return back()->with('success', "User {$user->name} downgraded to free tier.");
    }
}
