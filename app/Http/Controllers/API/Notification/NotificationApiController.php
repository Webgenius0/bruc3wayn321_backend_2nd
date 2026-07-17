<?php

namespace App\Http\Controllers\API\Notification;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessage;

class NotificationApiController extends Controller
{
    use ApiResponse;

    /**
     * List all database notifications and mark them as read.
     */
    public function list()
    {
        $user = auth()->user();

        $notifications = $user->notifications()->paginate(15);

        // Mark all as read when list is visited
        $user->unreadNotifications->markAsRead();

        return $this->successResponse('Notifications list', $notifications);
    }

    /**
     * Get unread notifications count and unread messages count.
     */
    public function unreadCounts()
    {
        $user = auth()->user();

        $unreadNotificationsCount = $user->unreadNotifications()->count();

        $unreadMessagesCount = ChatMessage::where('is_read', false)
            ->where('sender_id', '!=', $user->id)
            ->whereHas('chat.users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        return $this->successResponse('Unread counts', [
            'unread_notifications' => $unreadNotificationsCount,
            'unread_messages' => $unreadMessagesCount,
        ]);
    }
}
