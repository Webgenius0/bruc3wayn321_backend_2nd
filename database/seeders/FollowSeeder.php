<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('status', true)->get();
        if ($users->count() < 2) {
            return;
        }

        foreach ($users as $user) {
            // Pick 3 to 7 other users to follow
            $toFollow = $users->where('id', '!=', $user->id)->random(min(fake()->numberBetween(3, 7), $users->count() - 1));

            foreach ($toFollow as $followingUser) {
                DB::table('follows')->updateOrInsert(
                    [
                        'follower_id' => $user->id,
                        'following_id' => $followingUser->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
