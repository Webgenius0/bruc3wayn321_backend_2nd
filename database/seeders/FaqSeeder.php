<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'What is Roots & Routes?',
                'answer' => 'Roots & Routes is your AI-powered travel and relocation companion. Whether you’re planning a vacation, moving abroad, or exploring a new city, our AI helps you discover destinations, create itineraries, compare countries, find activities, and access trusted travel resources—all in one app.',
            ],
            [
                'question' => 'How do I use the AI Concierge?',
                'answer' => "Simply type or speak your question. Ask things like:\n\n• Plan a 7-day trip to Thailand.\n• What’s the safest neighborhood in Lisbon?\n• What’s the cost of living in Tokyo?\n\nThe AI provides personalized recommendations based on your preferences and travel goals.",
            ],
            [
                'question' => 'How do I plan a trip?',
                'answer' => 'Tell the AI where you’re going, your travel dates, budget, and interests. It can recommend flights, hotels, activities, restaurants, transportation, and build a complete itinerary that you can save for later.',
            ],
            [
                'question' => 'How do bookings work?',
                'answer' => 'Roots & Routes helps you discover flights, hotels, tours, and experiences through trusted travel partners. When you’re ready to book, you’ll be securely redirected to the partner’s website to complete your reservation.',
            ],
            [
                'question' => 'How do I upgrade or cancel my subscription?',
                'answer' => 'Go to Profile → Settings → Subscription to view your current plan, upgrade, downgrade, or cancel. Any changes will take effect according to your billing cycle.',
            ],
            [
                'question' => 'Can Roots & Routes help me move abroad?',
                'answer' => 'Yes. The AI can compare countries, estimate living costs, explain visa requirements, recommend neighborhoods, suggest schools and healthcare options, and help you build a personalized relocation plan.',
            ],
            [
                'question' => 'How do I contact support?',
                'answer' => "Need help? Go to Profile → Help → Contact Support to report a bug, ask a question, or request a new feature.\n\nPlease email: rootsandroutes12@gmail.com",
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'sort_order' => $index + 1,
                    'status' => true,
                ]
            );
        }
    }
}
