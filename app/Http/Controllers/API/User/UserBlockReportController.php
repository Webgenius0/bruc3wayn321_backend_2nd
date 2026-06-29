<?php

namespace App\Http\Controllers\API\User;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserBlockReportController extends Controller
{
    use ApiResponse;

    /**
     * Block a user
     */
    public function block(Request $request, $userId)
    {
        $authUser = Auth::user();

        if ($authUser->id == $userId) {
            return $this->errorResponse('You cannot block yourself', 422);
        }

        $targetUser = User::find($userId);
        if (!$targetUser) {
            return $this->errorResponse('User not found', 404);
        }

        $alreadyBlocked = UserBlock::where('user_id', $authUser->id)
            ->where('blocked_user_id', $userId)
            ->exists();

        if ($alreadyBlocked) {
            return $this->errorResponse('User is already blocked', 409);
        }

        UserBlock::create([
            'user_id'         => $authUser->id,
            'blocked_user_id' => $userId,
        ]);

        return $this->successResponse('User blocked successfully', [
            'blocked_user_id' => (int) $userId,
        ]);
    }

    /**
     * Unblock a user
     */
    public function unblock(Request $request, $userId)
    {
        $authUser = Auth::user();

        $block = UserBlock::where('user_id', $authUser->id)
            ->where('blocked_user_id', $userId)
            ->first();

        if (!$block) {
            return $this->errorResponse('This user is not blocked', 409);
        }

        $block->delete();

        return $this->successResponse('User unblocked successfully', [
            'unblocked_user_id' => (int) $userId,
        ]);
    }

    /**
     * Toggle block status of a user
     */
    public function toggleBlock(Request $request, $userId)
    {
        $authUser = Auth::user();

        if ($authUser->id == $userId) {
            return $this->errorResponse('You cannot block yourself', 422);
        }

        $targetUser = User::find($userId);
        if (!$targetUser) {
            return $this->errorResponse('User not found', 404);
        }

        $block = UserBlock::where('user_id', $authUser->id)
            ->where('blocked_user_id', $userId)
            ->first();

        if ($block) {
            $block->delete();
            return $this->successResponse('User unblocked successfully', [
                'user_id'    => (int) $userId,
                'is_blocked' => false,
            ]);
        }

        UserBlock::create([
            'user_id'         => $authUser->id,
            'blocked_user_id' => $userId,
        ]);

        return $this->successResponse('User blocked successfully', [
            'user_id'    => (int) $userId,
            'is_blocked' => true,
        ]);
    }

    /**
     * Get list of blocked users
     */
    public function blockedList(Request $request)
    {
        $authUser = Auth::user();

        $blockedUsers = UserBlock::where('user_id', $authUser->id)
            ->with('blockedUser:id,first_name,last_name,user_name,email,avatar')
            ->get()
            ->map(function ($block) {
                return [
                    'block_id'     => $block->id,
                    'blocked_at'   => $block->created_at,
                    'blocked_user' => $block->blockedUser,
                ];
            });

        return $this->successResponse('Blocked users list', $blockedUsers);
    }

    /**
     * Report a user
     */
    public function report(Request $request, $userId)
    {
        $authUser = Auth::user();

        if ($authUser->id == $userId) {
            return $this->errorResponse('You cannot report yourself', 422);
        }

        $targetUser = User::find($userId);
        if (!$targetUser) {
            return $this->errorResponse('User not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $report = UserReport::create([
            'reporter_id'      => $authUser->id,
            'reported_user_id' => $userId,
            'description'      => $request->description,
            'status'           => 'pending',
        ]);

        return $this->successResponse('User reported successfully', [
            'report_id'        => $report->id,
            'reported_user_id' => (int) $userId,
            'status'           => $report->status,
        ]);
    }

    /**
     * Check block status between auth user and target user
     */
    public function blockStatus(Request $request, $userId)
    {
        $authId = Auth::id();

        $iBlockedThem = UserBlock::where('user_id', $authId)
            ->where('blocked_user_id', $userId)
            ->exists();

        $theyBlockedMe = UserBlock::where('user_id', $userId)
            ->where('blocked_user_id', $authId)
            ->exists();

        return $this->successResponse('Block status', [
            'user_id'         => (int) $userId,
            'i_blocked_them'  => $iBlockedThem,
            'they_blocked_me' => $theyBlockedMe,
            'is_blocked'      => $iBlockedThem || $theyBlockedMe,
        ]);
    }
}
