<?php

namespace App\Http\Controllers\API\Auth;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class SocialAuthController extends Controller
{
    use ApiResponse;
    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required',
            'token' => 'required',
        ]);
        // dd($request->all());

        try {
            $socialUser = Socialite::driver($request->provider)->stateless()->userFromToken($request->token);

            $raw = method_exists($socialUser, 'getRaw')
                ? $socialUser->getRaw()
                : $socialUser->user;

            $firstName = $raw['given_name']
                ?? $raw['first_name']
                ?? explode(' ', $socialUser->getName(), 2)[0]
                ?? '';

            $lastName = $raw['family_name']
                ?? $raw['last_name']
                ?? (count(explode(' ', $socialUser->getName(), 2)) > 1
                    ? explode(' ', $socialUser->getName(), 2)[1]
                    : '');

            // Check if user exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                do {
                    $userName = Str::slug(
                        $firstName . $lastName,
                        ''
                    ) . rand(1000, 9999);
                } while (User::where('user_name', $userName)->exists());
                $user = User::create([
                    'social_id' => $socialUser->getId(),
                    'provider' => $request->provider,
                    'first_name'      => $firstName,
                    'last_name'      => $lastName,
                    'user_name'      => $userName,
                    'email'     => $socialUser->getEmail(),
                    'role'     => 'user',
                    'avatar'    => $socialUser->getAvatar(),
                    'password'  => bcrypt(Str::random(32)),
                ]);
            } else {
                // Update Google info if already exists
                $user->update([
                    'social_id' => $socialUser->getId(),
                    'avatar'    => $socialUser->getAvatar(),
                ]);
            }

            $loginUser = User::find($user->id);

            $token = $loginUser->createToken('auth_token')->plainTextToken;

            return $this->successResponse("Login Successfull", [
                "token" => $token,
                "user" => $loginUser,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong.',
                'errors' => [],
            ], 500);
        }
    }
}
