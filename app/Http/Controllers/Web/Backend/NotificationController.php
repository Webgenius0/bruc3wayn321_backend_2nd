<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display the notifications view.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->notifications()->paginate(15);
        
        // Mark all notifications as read when visiting the list
        $user->unreadNotifications->markAsRead();

        return Inertia::render('backend/notifications/index', [
            'notifications' => $notifications,
        ]);
    }
}
