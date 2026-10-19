<?php

namespace Database\Seeders;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiConciergeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $conversationsData = [
            [
                'title' => '7-Day Tokyo & Kyoto Culinary Itinerary',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Can you plan a 7-day culinary and culture itinerary for my upcoming trip to Tokyo and Kyoto? I want authentic local spots, not just tourist traps.',
                    ],
                    [
                        'role' => 'assistant',
                        'content' => "Here is your curated 7-day culinary and cultural itinerary for Tokyo & Kyoto:\n\n### Days 1–4: Tokyo\n• **Day 1 (Shinjuku & Shibuya)**: Arrive, check in. Dinner: Tsuta or Fuunji (Shinjuku) for world-class tsukemen ramen. Evening stroll through Omoide Yokocho for yakitori skewers.\n• **Day 2 (Asakusa & Ueno)**: Morning at Senso-ji temple, street snack tour along Nakamise-dori (try freshly grilled ningyo-yaki and matcha dango). Dinner in Yanaka at a traditional izakaya.\n• **Day 3 (Tsukiji & Ginza)**: 8 AM breakfast at Tsukiji Outer Market for fresh sea urchin (uni) and tamagoyaki. Afternoon coffee in Ginza; evening cocktails at a hidden Bar Trench in Ebisu.\n• **Day 4 (Kichijoji & Shimokitazawa)**: Explore Inokashira Park and thrift in Shimokitazawa. Catch the afternoon Shinkansen bullet train to Kyoto (2h 15m).\n\n### Days 5–7: Kyoto\n• **Day 5 (Gion & Higashiyama)**: Morning tea ceremony in Higashiyama; lunch at a classic soba house near Yasaka Shrine. Kaiseki dinner in Gion.\n• **Day 6 (Arashiyama & Nishiki Market)**: Early morning bamboo forest walk; afternoon tasting walk through Nishiki Market (pickled veggies, takoyaki, tofu donuts).\n• **Day 7 (Fushimi Inari & Uji)**: 6:30 AM hike up Fushimi Inari shrine. Afternoon day-trip to Uji for green tea tastings before heading to Kansai International Airport (KIX).",
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Do I need advance reservations for the Kaiseki dinner in Gion?',
                    ],
                    [
                        'role' => 'assistant',
                        'content' => "Yes, absolutely! Top-tier Kaiseki establishments in Gion typically seat between 8 to 15 guests and accept reservations 1 to 2 months in advance. Many require booking through your hotel concierge or services like Pocket Concierge / Omakase. I recommend booking Gion Karyo or Roan Kikunoi at least 30 days before your arrival date.",
                    ],
                ],
            ],
            [
                'title' => 'Moving to Portugal: D8 Digital Nomad Visa Guide',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'I am a remote software developer considering relocating to Portugal on the D8 Digital Nomad Visa. What are the income requirements and required documents?',
                    ],
                    [
                        'role' => 'assistant',
                        'content' => "Portugal's Digital Nomad Visa (D8 Visa) is one of the most accessible in Europe for remote workers. Here is the full breakdown:\n\n### 1. Income Threshold\n• **Monthly Income Requirement**: You must demonstrate that your regular income from outside Portugal is at least **4 times the Portuguese national minimum wage**. For 2026, this is approximately **€3,280 – €3,400/month**.\n\n### 2. Core Documents Needed\n• Proof of employment or independent contract with non-Portuguese entities.\n• Last 3–6 months of bank statements showing regular deposits.\n• Tax returns from your home country.\n• Proof of accommodation in Portugal for at least 12 months (e.g., registered lease agreement or promissory contract).\n• NIF (Portuguese Tax Number) and Portuguese bank account with savings buffer (recommended €10,000+ per applicant).\n• Clean criminal background check with Apostille certification.\n• Travel & international health insurance covering repatriation.",
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Can my spouse join me on the same application?',
                    ],
                    [
                        'role' => 'assistant',
                        'content' => "Yes! Your spouse can accompany you via family reunification. The primary applicant needs to add an additional 50% of the minimum wage (~€410/month) for the spouse and 30% (~€246/month) for each dependent child to the qualifying income threshold.",
                    ],
                ],
            ],
            [
                'title' => 'Best Neighborhoods in Lisbon for Remote Workers',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Which neighborhoods in Lisbon have the best combination of fast WiFi, cafes, walkability, and safety for digital nomads?',
                    ],
                    [
                        'role' => 'assistant',
                        'content' => "Here are the top 4 neighborhoods in Lisbon for remote workers:\n\n1. **Príncipe Real**: Leafy, upscale, and filled with specialty cafes, concept stores, and lush parks. Walkable to Bairro Alto without the late-night noise.\n2. **Santos & Estrela**: Creative design district with fantastic coworking spaces like Second Home and LACS. Great brunch spots and flat access along the riverfront.\n3. **Saldanha & Avenidas Novas**: Modern business district with wider avenues, fiber-optic internet in almost every apartment, modern gyms, and excellent metro connectivity.\n4. **Intendente & Anjos**: Edgy, multicultural, and vibrant. Lower rent than Chiado, booming arts scene, and packed with alternative coworking cafes like Dear Breakfast and The Folks.",
                    ],
                ],
            ],
        ];

        // Seed conversations across multiple users
        foreach ($users->take(4) as $uIndex => $user) {
            $convData = $conversationsData[$uIndex % count($conversationsData)];

            $conversation = AiConversation::create([
                'user_id' => $user->id,
                'title' => $convData['title'],
            ]);

            foreach ($convData['messages'] as $msg) {
                AiMessage::create([
                    'ai_conversation_id' => $conversation->id,
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ]);
            }
        }
    }
}
