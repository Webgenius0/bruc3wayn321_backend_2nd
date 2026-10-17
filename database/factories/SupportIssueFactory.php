<?php

namespace Database\Factories;

use App\Models\SupportIssue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportIssue>
 */
class SupportIssueFactory extends Factory
{
    protected $model = SupportIssue::class;

    public function definition(): array
    {
        $issues = [
            [
                'subject' => 'Offline maps download is failing on iOS',
                'message' => 'Whenever I try downloading the offline city map for Tokyo before my flight, the progress bar hangs at 78% and then fails with a network timeout error. Can you help?',
                'reply' => 'Hi! We investigated this and pushed an update to our map tile CDN. Please try refreshing or updating to the latest app build. Let us know if you need anything else!',
            ],
            [
                'subject' => 'Question about Portugal D8 Nomad Visa checklist',
                'message' => 'The AI concierge suggested an income threshold of €3,280 for the Portugal D8 visa. Is this for single applicants or does it include dependents?',
                'reply' => 'Hello! That threshold is for the primary applicant. For a spouse or dependent adult, you should add 50%, and for dependent children add 30%. We have updated the concierge prompt to clarify this explicitly.',
            ],
            [
                'subject' => 'Subscription billing inquiry - annual plan discount',
                'message' => 'I was charged for the monthly plan yesterday, but I meant to select the annual plan with the 30% discount. Is it possible to switch and apply the difference?',
                'reply' => 'Hello! Absolutely. We have upgraded your account to the annual tier and credited the monthly charge toward your new balance. Welcome to Roots & Routes Premium!',
            ],
            [
                'subject' => 'Feature Request: Export itinerary to Apple & Google Calendar',
                'message' => 'I love the AI travel itinerary generator! Would it be possible to add a button to export the entire 7-day schedule directly into Apple Calendar or Google Calendar (.ics)?',
                'reply' => 'Thank you so much for the feedback! Our product team is already designing the .ics calendar export feature and it will be available in the upcoming v2.4 release.',
            ],
            [
                'subject' => 'Community feed video upload issue',
                'message' => 'I tried uploading a 30-second drone video of the Amalfi Coast to my travel post, but got an "unsupported format" error even though it was an .mp4 file.',
                'reply' => null,
            ],
            [
                'subject' => 'App language translation for local menus',
                'message' => 'Is there an upcoming feature where we can use our camera to translate foreign menus in real-time when traveling in Japan or Korea?',
                'reply' => null,
            ],
        ];

        $choice = fake()->randomElement($issues);
        $isResolved = !empty($choice['reply']);

        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => $choice['subject'],
            'message' => $choice['message'],
            'status' => $isResolved ? 'resolved' : fake()->randomElement(['open', 'in_progress']),
            'reply' => $choice['reply'],
            'replied_by' => $isResolved ? 1 : null,
            'replied_at' => $isResolved ? now()->subDays(fake()->numberBetween(1, 10)) : null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'reply' => null,
            'replied_by' => null,
            'replied_at' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'reply' => 'Thank you for reaching out! This issue has been fully resolved by our support team.',
            'replied_by' => 1,
            'replied_at' => now(),
        ]);
    }
}
