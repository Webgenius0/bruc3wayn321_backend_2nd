<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $require  - 'any' (free or paid), 'paid' (RevenueCat only), 'free' (free only)
     */
    public function handle(Request $request, Closure $next, string $require = 'any'): Response
    {
        $user = $request->user();

        // 'any' = requires free OR paid active subscription
        if ($require === 'any') {
            if (!$user->is_subscribed) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need an active subscription (free or paid) to access this feature.',
                    'required' => 'any',
                ], 403);
            }
            return $next($request);
        }

        // 'paid' = requires paid RevenueCat subscription specifically
        if ($require === 'paid') {
            if (!$user->hasPaidSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A paid subscription is required to access this feature.',
                    'required' => 'paid',
                ], 403);
            }
            return $next($request);
        }

        // 'free' = requires free subscription specifically (rare)
        if ($require === 'free') {
            if (!$user->hasFreeSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A free subscription is required to access this feature.',
                    'required' => 'free',
                ], 403);
            }
            return $next($request);
        }

        return $next($request);
    }
}
