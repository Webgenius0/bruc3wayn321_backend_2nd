<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\UserReport;
use App\Models\PostLike;
use App\Models\PostComment;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function home(Request $request)
    {
        return redirect()->route('admin.dashboard.index');
    }

    public function index(Request $request)
    {
        $totalUsers        = User::count();
        $activeUsers       = User::where('status', true)->count();
        $suspendedUsers    = User::where('status', false)->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count();

        $totalPosts        = Post::count();
        $publishedPosts    = Post::where('is_published', true)->count();
        $draftPosts        = Post::where('is_draft', true)->count();
        $postsThisMonth    = Post::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count();

        $totalReports      = UserReport::count();
        $pendingReports    = UserReport::where('status', 'pending')->count();
        $resolvedReports   = UserReport::where('status', 'resolved')->count();

        $totalLikes        = PostLike::count();
        $totalComments     = PostComment::count();
        $totalFollows      = Follow::count();

        // New users per month for the current year
        $usersPerMonth = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $usersMonthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $usersMonthlyData[] = $usersPerMonth[$m] ?? 0;
        }

        // Posts per month for current year
        $postsPerMonth = Post::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $postsMonthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $postsMonthlyData[] = $postsPerMonth[$m] ?? 0;
        }

        // Top active users (most posts)
        $topUsers = User::withCount('posts')
            ->orderByDesc('posts_count')
            ->take(5)
            ->get(['id', 'first_name', 'last_name', 'email', 'avatar', 'created_at']);

        // Recent users
        $recentUsers = User::latest()
            ->take(5)
            ->get(['id', 'first_name', 'last_name', 'email', 'avatar', 'status', 'created_at']);

        // Recent posts
        $recentPosts = Post::with('user:id,first_name,last_name,avatar')
            ->latest()
            ->take(5)
            ->get(['id', 'slug', 'content', 'location', 'is_published', 'is_draft', 'user_id', 'created_at']);

        // Recent reports
        $recentReports = UserReport::with(['reporter:id,first_name,last_name', 'reportedUser:id,first_name,last_name'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Recent notifications (admin user)
        $notifications = auth()->user()->notifications()->latest()->take(8)->get();

        // Community posts (published) for post feed
        $communityPosts = Post::with('user:id,first_name,last_name,avatar')
            ->where('is_published', true)
            ->withCount(['likes', 'comments'])
            ->latest()
            ->take(8)
            ->get(['id', 'slug', 'content', 'location', 'images', 'user_id', 'created_at']);

        return Inertia::render('backend/dashboard/index', [
            'stats' => [
                'totalUsers'        => $totalUsers,
                'activeUsers'       => $activeUsers,
                'suspendedUsers'    => $suspendedUsers,
                'newUsersThisMonth' => $newUsersThisMonth,
                'totalPosts'        => $totalPosts,
                'publishedPosts'    => $publishedPosts,
                'draftPosts'        => $draftPosts,
                'postsThisMonth'    => $postsThisMonth,
                'totalReports'      => $totalReports,
                'pendingReports'    => $pendingReports,
                'resolvedReports'   => $resolvedReports,
                'totalLikes'        => $totalLikes,
                'totalComments'     => $totalComments,
                'totalFollows'      => $totalFollows,
            ],
            'charts' => [
                'usersMonthlyData' => $usersMonthlyData,
                'postsMonthlyData' => $postsMonthlyData,
            ],
            'topUsers'         => $topUsers,
            'recentUsers'      => $recentUsers,
            'recentPosts'      => $recentPosts,
            'recentReports'    => $recentReports,
            'notifications'    => $notifications,
            'communityPosts'   => $communityPosts,
        ]);
    }
}
