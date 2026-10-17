<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReport;
use Illuminate\Database\Seeder;

class UserReportSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::where('role', 'super_admin')->first();
        $spammer = User::where('email', 'spammer@example.com')->first();
        $david = User::where('email', 'user@gmail.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();
        $elena = User::where('email', 'elena@example.com')->first();

        if (!$spammer || !$david || !$sarah) {
            return;
        }

        // 1. Report from David against Spammer
        UserReport::create([
            'reporter_id' => $david->id,
            'reported_user_id' => $spammer->id,
            'description' => 'User sent repeated unsolicited direct messages promoting shady flight ticket discounts and third-party booking scams.',
            'status' => 'reviewed',
            'admin_note' => 'Confirmed bulk spam messages sent to 14 members. Account has been deactivated pending verification.',
            'reviewed_at' => now()->subDays(1),
            'reviewer_id' => $admin?->id,
        ]);

        // 2. Report from Sarah against Spammer
        UserReport::create([
            'reporter_id' => $sarah->id,
            'reported_user_id' => $spammer->id,
            'description' => 'Leaving copy-pasted promotional marketing links in the comments of my Bali travel post.',
            'status' => 'resolved',
            'admin_note' => 'Spam comments purged and warnings logged.',
            'reviewed_at' => now()->subHours(12),
            'reviewer_id' => $admin?->id,
        ]);

        // 3. Pending report from Elena for testing admin action
        if ($elena) {
            UserReport::create([
                'reporter_id' => $elena->id,
                'reported_user_id' => $spammer->id,
                'description' => 'Suspected bot account with fake profile picture scraping travel itinerary details.',
                'status' => 'pending',
                'admin_note' => null,
                'reviewed_at' => null,
                'reviewer_id' => null,
            ]);
        }

        // User Blocks
        UserBlock::firstOrCreate([
            'user_id' => $david->id,
            'blocked_user_id' => $spammer->id,
        ]);

        UserBlock::firstOrCreate([
            'user_id' => $sarah->id,
            'blocked_user_id' => $spammer->id,
        ]);
    }
}
