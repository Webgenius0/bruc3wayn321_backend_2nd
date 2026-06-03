<?php

namespace App\Http\Controllers\API\User;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePassword;
use App\Http\Requests\UserUpdateRequest;
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
}
