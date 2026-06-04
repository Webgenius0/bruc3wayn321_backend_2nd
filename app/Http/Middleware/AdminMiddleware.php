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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // Sanctum user

        if (!$user || $user->role !== 'admin') {

            if ($request->expectsJson() || $request->is('api/*')) {
                // API: revoke token
                if ($user && $request->bearerToken()) {
                    $request->user()->currentAccessToken()->delete();
                }
                return response()->json(['message' => 'Unauthorized'], 403);
            } else {
                // Web: log out

                // Web / Session guard
                if (Auth::guard('web')->check()) {
                    Auth::guard('web')->logout();
                }
                return redirect()->route('login')->with('t-error', 'Unauthorized access');
            }
        }



        return $next($request);
    }
}
