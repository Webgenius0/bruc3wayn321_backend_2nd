<?php

namespace App\Http\Controllers\API\User;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePassword;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $service
    ) {}

    private array $notificationFields = [
        'email_notifications',
        'push_notifications',
        'sms_notifications',
        'match_notifications',
        'message_notifications',
        'like_notifications',
        'marketing_notifications',
    ];

    public function profile()
    {
        $user = Auth::user();
        return $this->successResponse('Profile details', $user);
    }

    public function update(UserUpdateRequest $request)
    {
        return $this->service->update(
            $request->user(),
            $request->validated()
        );
    }

    public function updatePassword(UpdatePassword $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse(
                'Current password is incorrect',
                422,
                [
                    'current_password' => [
                        'Current password is incorrect',
                    ],
                ]
            );
        }

        $user->update([
            'password' => $request->password,
        ]);

        return $this->successResponse(
            'Password updated successfully'
        );
    }

    public function updateNotifications(Request $request)
    {
        $rules = collect($this->notificationFields)
            ->mapWithKeys(fn($field) => [
                $field => ['sometimes', 'required', 'boolean'],
            ])
            ->toArray();

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $validator->errors()
            );
        }

        $data = $validator->validated();

        if (empty($data)) {
            return $this->errorResponse(
                'Please provide at least one notification setting',
                422
            );
        }

        $user = $request->user();
        $user->update($data);

        return $this->successResponse(
            'Notification settings updated successfully',
            $user->fresh()->only($this->notificationFields)
        );
    }

    public function destroy(Request $request)
    {
        return $this->service->delete($request->user());
    }

    /**
     * Follow a user
     */
    public function follow(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Prevent users from following themselves
        if ($currentUser->id === $user->id) {
            return $this->errorResponse(
                'You cannot follow yourself',
                422
            );
        }

        // Check if already following
        if ($currentUser->isFollowing($user)) {
            return $this->errorResponse(
                'You are already following this user',
                409
            );
        }

        // Create follow relationship
        $currentUser->following()->attach($user->id);

        return $this->successResponse(
            'Successfully followed user',
            [
                'user_id' => $user->id,
                'followers_count' => $user->getFollowersCount(),
                'following_count' => $currentUser->getFollowingCount(),
            ]
        );
    }

    /**
     * Unfollow a user
     */
    public function unfollow(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Check if not following
        if (!$currentUser->isFollowing($user)) {
            return $this->errorResponse(
                'You are not following this user',
                409
            );
        }

        // Remove follow relationship
        $currentUser->following()->detach($user->id);

        return $this->successResponse(
            'Successfully unfollowed user',
            [
                'user_id' => $user->id,
                'followers_count' => $user->getFollowersCount(),
                'following_count' => $currentUser->getFollowingCount(),
            ]
        );
    }

    /**
     * Toggle follow status
     */
    public function toggleFollow(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Prevent users from following themselves
        if ($currentUser->id === $user->id) {
            return $this->errorResponse(
                'You cannot follow yourself',
                422
            );
        }

        $isFollowing = $currentUser->isFollowing($user);

        if ($isFollowing) {
            $currentUser->following()->detach($user->id);
            $message = 'Successfully unfollowed user';
        } else {
            $currentUser->following()->attach($user->id);
            $message = 'Successfully followed user';
        }

        return $this->successResponse(
            $message,
            [
                'user_id' => $user->id,
                'is_following' => !$isFollowing,
                'followers_count' => $user->getFollowersCount(),
                'following_count' => $currentUser->getFollowingCount(),
            ]
        );
    }

    /**
     * Get user followers
     */
    public function getFollowers(Request $request, User $user)
    {
        $followers = $user->followers()
            ->select('users.id', 'users.name', 'users.user_name', 'users.avatar')
            ->paginate(15);

        return $this->successResponse(
            'Followers retrieved',
            $followers
        );
    }

    /**
     * Get user following
     */
    public function getFollowing(Request $request, User $user)
    {
        $following = $user->following()
            ->select('users.id', 'users.name', 'users.user_name', 'users.avatar')
            ->paginate(15);

        return $this->successResponse(
            'Following retrieved',
            $following
        );
    }
    public function updateCoverImage(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'cover_image' => 'required'
        ]);

        if ($validate->fails()) {
            return $this->errorResponse($validate->errors()->first(), 422, $validate->errors());
        }

        $image = $request->cover_image;

        $fileName = time() . "." . $image->getClientOriginalExtension();

        $path = $image->storeAs('cover-image', $fileName, 'public');

        $storagePath = "storage/" . $path;

        // dd($storagePath);
        $user = auth()->user();


        $following = $user->update([
            'cover_image' => $storagePath,
        ]);

        return $this->successResponse(
            'Successfully update cover image',
            $following
        );
    }
    public function updateCurrency(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'currency_code' => 'required'
        ]);

        if ($validate->fails()) {
            return $this->errorResponse($validate->errors()->first(), 422, $validate->errors());
        }


        $user = auth()->user();


        $following = $user->update([
            'currency' => $request->currency_code,
        ]);

        return $this->successResponse(
            'Successfully update cover image',
            $following
        );
    }
}
