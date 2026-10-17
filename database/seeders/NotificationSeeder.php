<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Notifications\NewMessageReceived;
use App\Notifications\PostCommented;
use App\Notifications\PostLiked;
use App\Notifications\ProfileUpdated;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::where('role', 'super_admin')->first();
        $david = User::where('email', 'user@gmail.com')->first();
        $elena = User::where('email', 'elena@example.com')->first();
        $kenji = User::where('email', 'kenji@example.com')->first();

        if (!$admin || !$david) {
            return;
        }

        $posts = Post::all();
        $samplePost = $posts->first();

        // 1. Notifications for Admin
        $adminNotifications = [
            [
                'title' => 'New User Registration',
                'message' => 'Elena Rostova registered as a new traveler from Lisbon.',
                'type' => 'user_registered',
                'metadata' => ['user_id' => $elena?->id],
            ],
            [
                'title' => 'New Support Ticket',
                'message' => 'David Miller opened a ticket: "Offline maps download is failing on iOS"',
                'type' => 'support_ticket',
                'metadata' => ['subject' => 'Offline maps download is failing on iOS'],
            ],
            [
                'title' => 'User Reported',
                'message' => 'Spammy Promoter was reported for commercial spamming in direct messages.',
                'type' => 'user_reported',
                'metadata' => ['reported_user' => 'cheap_tickets_now'],
            ],
            [
                'title' => 'Subscription Upgraded',
                'message' => 'David Miller upgraded to the Annual Premium Plan ($59.99/yr).',
                'type' => 'subscription',
                'metadata' => ['plan' => 'rc_premium_yearly'],
            ],
        ];

        foreach ($adminNotifications as $notif) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\Notifications\AdminAlertNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $admin->id,
                'data' => json_encode($notif),
                'read_at' => null,
                'created_at' => now()->subHours(fake()->numberBetween(1, 24)),
                'updated_at' => now(),
            ]);
        }

        // 2. Notifications for David (Test User)
        if ($samplePost && $elena) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => PostLiked::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $david->id,
                'data' => json_encode([
                    'title' => 'Post Liked',
                    'message' => "{$elena->first_name} liked your post \"{$samplePost->location}\".",
                    'type' => 'like',
                    'metadata' => [
                        'post_id' => $samplePost->id,
                        'post_slug' => $samplePost->slug,
                        'liker_id' => $elena->id,
                    ],
                ]),
                'read_at' => null,
                'created_at' => now()->subHours(2),
                'updated_at' => now(),
            ]);
        }

        if ($samplePost && $kenji) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => PostCommented::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $david->id,
                'data' => json_encode([
                    'title' => 'New Comment',
                    'message' => "{$kenji->first_name} commented: \"What a gorgeous view! Definitely putting this on my itinerary 🌟\"",
                    'type' => 'comment',
                    'metadata' => [
                        'post_id' => $samplePost->id,
                        'post_slug' => $samplePost->slug,
                        'commenter_id' => $kenji->id,
                    ],
                ]),
                'read_at' => null,
                'created_at' => now()->subHours(1),
                'updated_at' => now(),
            ]);
        }
    }
}
