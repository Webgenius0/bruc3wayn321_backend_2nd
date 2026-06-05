<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // Works for both Sanctum & session

        if (!$user || $user->role !== 'admin') {

            // ✅ API Requests (Sanctum / SPA API)
            if ($request->expectsJson() || $request->is('api/*')) {

                if ($user && $request->bearerToken()) {
                    $user->currentAccessToken()?->delete();
                }

                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            // ✅ Inertia (Web Requests)
            Auth::guard('web')->logout();

            // Important: Inertia needs a redirect, not JSON
            return redirect()
                ->route('login')
                ->with('error', 'Unauthorized access');
        }

        return $next($request);
    }
}
