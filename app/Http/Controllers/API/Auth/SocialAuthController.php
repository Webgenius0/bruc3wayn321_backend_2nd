<?php

namespace App\Http\Controllers\API\Auth;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class SocialAuthController extends Controller
{
    use ApiResponse;

    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider'   => 'required|in:google,apple',
            'token'      => 'required|string',
            'first_name' => 'nullable|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'name'       => 'nullable|string|max:255',
            'email'      => 'nullable|email',
        ]);

        try {
            $payload = [];
            if ($request->provider === 'apple') {
                $jwtParts = explode('.', $request->token);
                if (count($jwtParts) === 3) {
                    $payload = json_decode(base64_decode(strtr($jwtParts[1], '-_', '+/')), true) ?: [];
                }

                // If apple client_id is not set or multiple allowed IDs exist, align it with token aud
                $configuredClientIds = array_filter(array_map('trim', explode(',', (string) config('services.apple.client_id'))));
                if (!empty($configuredClientIds)) {
                    if (isset($payload['aud']) && in_array($payload['aud'], $configuredClientIds, true)) {
                        config(['services.apple.client_id' => $payload['aud']]);
                    }
                } elseif (!empty($payload['aud'])) {
                    config(['services.apple.client_id' => $payload['aud']]);
                }
            }

            $socialUser = Socialite::driver($request->provider)->stateless()->userFromToken($request->token);

            $raw = method_exists($socialUser, 'getRaw')
                ? $socialUser->getRaw()
                : $socialUser->user;

            $socialId = (string) $socialUser->getId();
            $email = $socialUser->getEmail() ?: ($payload['email'] ?? $request->input('email'));

            $firstName = '';
            $lastName = '';
            $avatar = null;

            if ($request->provider === 'apple') {
                if ($request->filled('first_name')) {
                    $firstName = (string) $request->input('first_name');
                    $lastName = (string) $request->input('last_name', '');
                } elseif ($request->filled('name')) {
                    $nameParts = explode(' ', (string) $request->input('name'), 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '';
                } else {
                    $firstName = $raw['given_name']
                        ?? $raw['first_name']
                        ?? ($email ? Str::title(Str::before($email, '@')) : 'AppleUser');
                    $lastName = $raw['family_name'] ?? $raw['last_name'] ?? '';
                }
                $avatar = null;
            } else {
                if ($request->filled('first_name')) {
                    $firstName = (string) $request->input('first_name');
                    $lastName = (string) $request->input('last_name', '');
                } elseif ($request->filled('name')) {
                    $nameParts = explode(' ', (string) $request->input('name'), 2);
                    $firstName = $nameParts[0];
                    $lastName = $nameParts[1] ?? '';
                } else {
                    $fullName = $socialUser->getName() ?: '';
                    $nameParts = explode(' ', $fullName, 2);
                    $firstName = $raw['given_name'] ?? $raw['first_name'] ?? ($nameParts[0] ?: 'User');
                    $lastName = $raw['family_name'] ?? $raw['last_name'] ?? ($nameParts[1] ?? '');
                }
                $avatar = $socialUser->getAvatar();
            }

            // 1. Check if user exists by provider & social_id
            $user = User::where('provider', $request->provider)
                ->where('social_id', $socialId)
                ->first();

            // 2. Fallback check by email if available
            if (!$user && !empty($email)) {
                $user = User::where('email', $email)->first();
            }

            if (!$user) {
                if (empty($email)) {
                    return $this->errorResponse('Email is required for registration. Please provide email.', 422);
                }

                do {
                    $userName = Str::slug($firstName . $lastName, '') . rand(1000, 9999);
                } while (User::where('user_name', $userName)->exists());

                $user = User::create([
                    'social_id'         => $socialId,
                    'provider'          => $request->provider,
                    'first_name'        => $firstName,
                    'last_name'         => $lastName,
                    'user_name'         => $userName,
                    'email'             => $email,
                    'role'              => 'user',
                    'avatar'            => $avatar,
                    'password'          => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                    'status'            => true,
                    'email_visibility'  => true,
                    'phone_visibility'  => true,
                ]);
            } else {
                $updateData = [
                    'social_id' => $socialId,
                    'provider'  => $request->provider,
                ];

                if (empty($user->first_name) && !empty($firstName)) {
                    $updateData['first_name'] = $firstName;
                }
                if (empty($user->last_name) && !empty($lastName)) {
                    $updateData['last_name'] = $lastName;
                }
                if (empty($user->getRawOriginal('avatar')) && !empty($avatar)) {
                    $updateData['avatar'] = $avatar;
                }
                if (empty($user->email_verified_at)) {
                    $updateData['email_verified_at'] = now();
                }

                $user->update($updateData);
            }

            $loginUser = $user->fresh();

            if (!$loginUser->status) {
                return $this->errorResponse('Your account has been suspended. Please contact support.', 403);
            }

            $token = $loginUser->createToken('api-token')->plainTextToken;

            return $this->successResponse("Login successful", [
                "token" => $token,
                "user"  => $loginUser,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Social Login Error: ' . $e->getMessage(), [
                'provider' => $request->provider,
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'code'    => 400,
                'message' => $e->getMessage() ?: 'Social login authentication failed.',
                'errors'  => [],
            ], 400);
        }
    }
}
