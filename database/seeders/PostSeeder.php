<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->where('status', true)->get();
        if ($users->isEmpty()) {
            return;
        }

        $postsData = [
            [
                'email' => 'kenji@example.com',
                'location' => 'Shibuya, Tokyo, Japan',
                'lat' => '35.6595',
                'lng' => '139.7005',
                'title' => 'Late night ramen and neon lights in Shibuya',
                'content' => "Exploring Shibuya at midnight hits completely different! Stopped by a tiny 8-seat ramen bar tucked away in an alleyway. Rich tonkotsu broth, perfectly springy noodles, and authentic vibes. If you are visiting Tokyo soon, skip the tourist traps and look for noren curtains in the back alleys 🍜🇯🇵",
                'images' => [
                    'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'sarah@example.com',
                'location' => 'Ubud, Bali, Indonesia',
                'lat' => '-8.5069',
                'lng' => '115.2625',
                'title' => 'Sunrise trek through the Tegallalang Rice Terraces',
                'content' => "Woke up at 5:30 AM to catch the morning mist rolling across the Tegallalang rice terraces. The green hues in early light are unreal! Pro-tip: arrive before 7 AM before the tour buses start rolling in. Followed up with fresh dragonfruit smoothie bowls in downtown Ubud 🌴🥑",
                'images' => [
                    'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'chloe@example.com',
                'location' => 'Montmartre, Paris, France',
                'lat' => '48.8867',
                'lng' => '2.3431',
                'title' => 'Cobblestone streets and morning espresso in Montmartre',
                'content' => "Montmartre always feels like stepping inside an oil painting. Climbing up the stairs towards Sacré-Cœur with warm pain au chocolat in hand. There's an accordion player near Place du Tertre playing classic French tunes. Paris in autumn is pure magic ✨🥐",
                'images' => [
                    'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1509356843151-3e7d96241e11?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'marco@example.com',
                'location' => 'Trastevere, Rome, Italy',
                'lat' => '41.8894',
                'lng' => '12.4700',
                'title' => 'The best Cacio e Pepe in Trastevere',
                'content' => "Found this quaint family-run osteria that has been operating since 1952. Simple ingredients done with absolute mastery: Pecorino Romano, black pepper, and al dente tonnarelli pasta. Washing it down with house red wine under ivy-covered walls 🍝🍷🇮🇹",
                'images' => [
                    'https://images.unsplash.com/photo-1552832230-c0197dd311b5?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'elena@example.com',
                'location' => 'Alfama, Lisbon, Portugal',
                'lat' => '38.7118',
                'lng' => '-9.1290',
                'title' => 'Getting lost in Alfama and watching sunset from Miradouro',
                'content' => "Yellow Tram 28 rattling past, the smell of roasted sardines, and soulful Fado music drifting through open windows. Portugal has such a warm, welcoming energy. Ended the day at Miradouro de Santa Luzia overlooking the Tagus River with pastel de nata 🇵🇹💛",
                'images' => [
                    'https://images.unsplash.com/photo-1513581166391-887a96ddeafd?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'user@gmail.com',
                'location' => 'Gràcia, Barcelona, Spain',
                'lat' => '41.4034',
                'lng' => '2.1574',
                'title' => 'Tapas crawl through the lively pedestrian plazas of Gràcia',
                'content' => "Gràcia feels like a charming village inside a bustling metropolis. No cars, just pedestrian lanes and vibrant plazas filled with locals chatting over patatas bravas, pimientos de padrón, and cold vermouth. One of my favorite bohemian neighborhoods in Europe! 🇪🇸🥘",
                'images' => [
                    'https://images.unsplash.com/photo-1583422409516-2895a77efded?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'maya@example.com',
                'location' => 'Old Town, Dubrovnik, Croatia',
                'lat' => '42.6412',
                'lng' => '18.1084',
                'title' => 'Walking the ancient medieval city walls of Dubrovnik',
                'content' => "Stunning Adriatic Sea panorama on one side and terracotta rooftops on the other! Walking the full 2km wall circuit takes about 2 hours. Go either right when they open or late afternoon to avoid the midday sun and cruise ship crowds 🏰🌊",
                'images' => [
                    'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'lucas@example.com',
                'location' => 'Bangkok, Thailand',
                'lat' => '13.7563',
                'lng' => '100.5018',
                'title' => 'Night market feast at Jodd Fairs Bangkok',
                'content' => "Sensory overload in the best possible way! Mango sticky rice, crispy pork belly, grilled squid skewers, and fresh coconut water. Bangkok's street food culture is peerless. Easy to take the MRT to Rama 9 and walk right in 🥭🌶️🇹🇭",
                'images' => [
                    'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'liam@example.com',
                'location' => 'Reykjavik & Golden Circle, Iceland',
                'lat' => '64.1466',
                'lng' => '-21.9426',
                'title' => 'Chasing the Northern Lights across the Golden Circle',
                'content' => "Green auroral curtains dancing across the Arctic sky at -4°C! Words cannot describe how surreal it feels in person. Rented a 4x4 camper and drove out past Thingvellir National Park to escape light pollution. Worth every freezing second 🌌❄️",
                'images' => [
                    'https://images.unsplash.com/photo-1529963183134-61a90db47eaf?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'user@gmail.com',
                'location' => 'Cape Town, South Africa',
                'lat' => '-33.9249',
                'lng' => '18.4241',
                'title' => 'Sunset hike up Lion\'s Head overlooking Camps Bay',
                'content' => "The 360-degree view of Table Mountain, the Atlantic seaboard, and downtown Cape Town is unbeatable. The final scramble with chains and ladders gets your adrenaline pumping, but the sunset colors over the ocean make it completely unforgettable 🇿🇦⛰️🌅",
                'images' => [
                    'https://images.unsplash.com/photo-1580618672591-eb180b1a973f?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'kenji@example.com',
                'location' => 'Arashiyama, Kyoto, Japan',
                'lat' => '35.0116',
                'lng' => '135.6777',
                'title' => 'Bamboo Grove & Zen Temples of Arashiyama',
                'content' => "The sound of wind rustling through towering green bamboo stalks is one of the most peaceful experiences on Earth. After the grove, sat quietly at Tenryu-ji Temple admiring the 14th-century pond garden. Kyoto never ceases to inspire tranquility 🎋⛩️",
                'images' => [
                    'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'email' => 'maya@example.com',
                'location' => 'Brooklyn Bridge, New York, USA',
                'lat' => '40.7061',
                'lng' => '-73.9969',
                'title' => 'Golden hour walk across the Brooklyn Bridge',
                'content' => "Walking from DUMBO into Manhattan as the skyline lights up against a pink and orange sky. New York has an unmistakable pulse that pushes you forward. Grabbed classic New York-style pizza at Juliana's right under the bridge afterwards 🗽🍕",
                'images' => [
                    'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
        ];

        $commentsPool = [
            'What a gorgeous view! Definitely putting this on my itinerary for next month 🌟',
            'Did you need an international driving permit to rent a scooter there?',
            'The food looks incredible! What was the name of the restaurant?',
            'How many days would you recommend spending in this area?',
            'Stunning photography! What camera and lens did you use for this shot?',
            'I was there last summer! Did you get a chance to check out the hidden beach nearby?',
            'Such helpful travel tips! Thanks for sharing this gem 🙌',
            'Is public transit easy to navigate there or do you recommend ride-hailing apps?',
            'Saving this post right now! Great recommendations.',
        ];

        foreach ($postsData as $postItem) {
            $author = User::where('email', $postItem['email'])->first() ?? $users->random();
            $slug = Str::slug($postItem['title']) . '-' . Str::lower(Str::random(5));

            $post = Post::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $author->id,
                    'content' => $postItem['content'],
                    'location' => $postItem['location'],
                    'latitude' => $postItem['lat'],
                    'longitude' => $postItem['lng'],
                    'images' => $postItem['images'],
                    'audios' => null,
                    'videos' => null,
                    'visibility' => true,
                    'is_draft' => false,
                    'is_published' => true,
                ]
            );

            // Seed Likes from other users
            $otherUsers = $users->where('id', '!=', $author->id)->shuffle();
            $likers = $otherUsers->take(fake()->numberBetween(3, 7));
            foreach ($likers as $liker) {
                PostLike::firstOrCreate([
                    'post_id' => $post->id,
                    'user_id' => $liker->id,
                ]);
            }

            // Seed Comments from other users
            $commenters = $otherUsers->take(fake()->numberBetween(2, 4));
            foreach ($commenters as $commenter) {
                PostComment::firstOrCreate(
                    [
                        'post_id' => $post->id,
                        'user_id' => $commenter->id,
                    ],
                    [
                        'comment' => fake()->randomElement($commentsPool),
                    ]
                );
            }

            // Seed Post Views
            foreach ($otherUsers->take(fake()->numberBetween(5, 10)) as $viewer) {
                PostView::firstOrCreate([
                    'post_id' => $post->id,
                    'user_id' => $viewer->id,
                ], [
                    'ip_address' => fake()->ipv4(),
                ]);
            }
        }
    }
}
