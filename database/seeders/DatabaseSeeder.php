<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Seeding Core Settings, Currencies, FAQs & Pages...');
        $this->call([
            SettingSeeder::class,
            CurrencySeeder::class,
            FaqSeeder::class,
            PageSeeder::class,
        ]);

        $this->command->info('Seeding Users & Profiles...');
        $this->call([
            UserSeeder::class,
        ]);

        $this->command->info('Seeding Posts, Likes, Comments & Views...');
        $this->call([
            PostSeeder::class,
        ]);

        $this->command->info('Seeding Destinations & Social Follows...');
        $this->call([
            UserDestinationSeeder::class,
            FollowSeeder::class,
        ]);

        $this->command->info('Seeding Subscriptions & Tier Usage Limits...');
        $this->call([
            SubscriptionSeeder::class,
        ]);

        $this->command->info('Seeding AI Concierge Conversations...');
        $this->call([
            AiConciergeSeeder::class,
        ]);

        $this->command->info('Seeding Chats & Real-time Messages...');
        $this->call([
            ChatSeeder::class,
        ]);

        $this->command->info('Seeding Support Issues & User Reports...');
        $this->call([
            SupportIssueSeeder::class,
            UserReportSeeder::class,
        ]);

        $this->command->info('Seeding In-app Notifications...');
        $this->call([
            NotificationSeeder::class,
        ]);

        $this->command->info('Database seeding completed successfully! All dummy data is ready for local testing.');
    }
}
