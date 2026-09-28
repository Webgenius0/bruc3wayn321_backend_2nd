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

        $query = $user->notifications();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $perPage = $request->input('per_page', 5);
        $notifications = $query->paginate($perPage)->withQueryString();

        // Mark all notifications as read when visiting the list
        $user->unreadNotifications->markAsRead();

        return Inertia::render('backend/notifications/index', [
            'notifications' => $notifications,
            'filters' => [
                'date' => $request->date,
                'per_page' => $perPage,
            ],
        ]);
    }
}
