<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'user_name' => 'superadmin',
                'bio' => 'Roots & Routes System Super Administrator',
                'phone' => '+15550001111',
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
                'status' => true,
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
                'cover_image' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1200&q=80',
                'currency' => 'USD',
                'currency_flug' => '🇺🇸',
                'email_verified_at' => now(),
            ]
        );

        // 2. Admin
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'first_name' => 'Alex',
                'last_name' => 'Mercer',
                'user_name' => 'alex_admin',
                'bio' => 'Roots & Routes Community Manager & Travel Editor',
                'phone' => '+15550002222',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => true,
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
                'cover_image' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1200&q=80',
                'currency' => 'USD',
                'currency_flug' => '🇺🇸',
                'email_verified_at' => now(),
            ]
        );

        // 3. Main Test User
        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'first_name' => 'David',
                'last_name' => 'Miller',
                'user_name' => 'david_travels',
                'bio' => 'Full-time digital nomad & landscape photographer exploring Southeast Asia 🎒📸',
                'phone' => '+15550003333',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'status' => true,
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
                'cover_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                'currency' => 'USD',
                'currency_flug' => '🇺🇸',
                'email_verified_at' => now(),
            ]
        );

        // 4. Realistic Travel Personas for rich testing
        $travelUsers = [
            [
                'first_name' => 'Elena',
                'last_name' => 'Rostova',
                'user_name' => 'elena.wander',
                'email' => 'elena@example.com',
                'bio' => 'Expat living in Lisbon 🇵🇹 | Architect & slow travel enthusiast. Sharing neighborhood guides & cafes.',
                'phone' => '+351912345678',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
                'currency' => 'EUR',
                'currency_flug' => '🇪🇺',
            ],
            [
                'first_name' => 'Kenji',
                'last_name' => 'Sato',
                'user_name' => 'kenji.tokyo',
                'email' => 'kenji@example.com',
                'bio' => 'Tokyo local guide ⛩️ | Ramen hunter, alleyway explorer, and night photographer 🍜🗼',
                'phone' => '+819012345678',
                'avatar' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=400&q=80',
                'currency' => 'JPY',
                'currency_flug' => '🇯🇵',
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Jenkins',
                'user_name' => 'sarah_remote',
                'email' => 'sarah@example.com',
                'bio' => 'Product designer working remotely from Bali 🌴 Co-working reviews & sunset spots enthusiast.',
                'phone' => '+6281234567890',
                'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80',
                'currency' => 'USD',
                'currency_flug' => '🇺🇸',
            ],
            [
                'first_name' => 'Marco',
                'last_name' => 'Rossi',
                'user_name' => 'marco_roma',
                'email' => 'marco@example.com',
                'bio' => 'Born in Rome, living across Mediterranean Europe 🇮🇹 Food historian and wine lover 🍷🍝',
                'phone' => '+390612345678',
                'avatar' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=400&q=80',
                'currency' => 'EUR',
                'currency_flug' => '🇪🇺',
            ],
            [
                'first_name' => 'Chloe',
                'last_name' => 'Dubois',
                'user_name' => 'chloe_paris',
                'email' => 'chloe@example.com',
                'bio' => 'Parisian writer & cultural journalist 🥐 Chronicling vintage markets & hidden museums in France.',
                'phone' => '+33612345678',
                'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=400&q=80',
                'currency' => 'EUR',
                'currency_flug' => '🇪🇺',
            ],
            [
                'first_name' => 'Liam',
                'last_name' => 'O\'Connor',
                'user_name' => 'liam_hiking',
                'email' => 'liam@example.com',
                'bio' => 'Mountaineer and outdoor guide 🏔️ Currently trekking the Swiss Alps & Patagonia. Ultra-light gear geek.',
                'phone' => '+447911123456',
                'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
                'currency' => 'GBP',
                'currency_flug' => '🇬🇧',
            ],
            [
                'first_name' => 'Maya',
                'last_name' => 'Patel',
                'user_name' => 'maya_journeys',
                'email' => 'maya@example.com',
                'bio' => 'Solo female traveler across 42 countries 🎒 Promoting safe, sustainable and budget-friendly journeys.',
                'phone' => '+14155551234',
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
                'currency' => 'USD',
                'currency_flug' => '🇺🇸',
            ],
            [
                'first_name' => 'Lucas',
                'last_name' => 'Silva',
                'user_name' => 'lucas_nomad',
                'email' => 'lucas@example.com',
                'bio' => 'Brazilian expat & surfer based in Canggu 🏄‍♂️ Tech nomad sharing tips on visas and coliving.',
                'phone' => '+5511987654321',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80',
                'currency' => 'USD',
                'currency_flug' => '🇺🇸',
            ],
            [
                'first_name' => 'Spammy',
                'last_name' => 'Promoter',
                'user_name' => 'cheap_tickets_now',
                'email' => 'spammer@example.com',
                'bio' => 'CHEAP FLIGHTS 24/7 DM ME FOR 80% OFF TRAVEL PACKAGES!!!',
                'phone' => '+18005559999',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80',
                'currency' => 'USD',
                'currency_flug' => '🇺🇸',
                'status' => false, // Banned/Inactive user for testing admin toggles
            ],
        ];

        foreach ($travelUsers as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                array_merge([
                    'password' => Hash::make('12345678'),
                    'role' => 'user',
                    'status' => true,
                    'cover_image' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80',
                    'email_verified_at' => now(),
                    'date_of_birth' => '1995-05-15',
                    'email_visibility' => true,
                    'phone_visibility' => false,
                    'can_activate_free_trial' => true,
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'match_notifications' => true,
                    'message_notifications' => true,
                    'like_notifications' => true,
                ], $user)
            );
        }
    }
}
