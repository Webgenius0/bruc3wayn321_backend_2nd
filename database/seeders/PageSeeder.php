<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => '<h2>1. Information We Collect</h2>
<p>Roots & Routes ("we", "our", or "us") respects your privacy. We collect information you provide directly to us when creating an account, saving travel destinations, publishing community posts, or interacting with our AI Concierge service.</p>

<h3>Account & Profile Information</h3>
<p>When you register, we collect your name, email address, username, profile photo, bio, preferred currency, and notification preferences.</p>

<h3>Location & Device Data</h3>
<p>With your explicit permission, we access your device GPS coordinates to suggest nearby attractions, weather forecasts, and local community posts. You can revoke location permissions at any time through your device settings.</p>

<h2>2. How We Use Your Information</h2>
<ul>
  <li>To provide personalized travel recommendations and itinerary planning via our AI Concierge.</li>
  <li>To enable community features including post publishing, comments, and direct messaging.</li>
  <li>To process subscriptions and manage usage tier limits.</li>
  <li>To safeguard our platform against spam, abuse, and fraudulent activities.</li>
</ul>

<h2>3. Data Protection & Security</h2>
<p>We implement industry-standard encryption protocols (TLS/SSL) for all data in transit and at rest. We do not sell your personal data to third-party advertisers.</p>

<h2>4. Contact Us</h2>
<p>If you have any questions regarding this Privacy Policy, please email our Data Protection Officer at: <strong>privacy@rootsandroutes.app</strong></p>',
                'status' => true,
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms & Conditions',
                'content' => '<h2>1. Acceptance of Terms</h2>
<p>By accessing or using the Roots & Routes mobile app and web application, you agree to be bound by these Terms & Conditions and our Privacy Policy.</p>

<h2>2. User Accounts & Responsibilities</h2>
<p>You are responsible for maintaining the confidentiality of your login credentials. You agree to use the service in compliance with all applicable local and international laws.</p>

<h2>3. Community Guidelines & Content Standards</h2>
<p>Users must not upload false, misleading, defamatory, offensive, or infringing material. We reserve the right to remove any post, comment, or account that violates our standards.</p>

<h2>4. AI Concierge Disclaimer</h2>
<p>Our AI Concierge provides travel, itinerary, and relocation recommendations for informational purposes only. Travel requirements, visa regulations, and business hours may change. Users must independently verify critical travel documentation with official embassies or service providers.</p>

<h2>5. Subscriptions & Billing</h2>
<p>Subscriptions renew automatically according to the selected plan unless canceled at least 24 hours before the end of the billing cycle through your App Store or Play Store account.</p>',
                'status' => true,
            ],
            [
                'slug' => 'about-us',
                'title' => 'About Roots & Routes',
                'content' => '<h2>Our Mission</h2>
<p>Roots & Routes was built for the modern global citizen—digital nomads, expats, backpackers, and slow travelers. We believe exploring the world should be seamless, inspiring, and deeply connected to local culture.</p>

<h2>What We Offer</h2>
<ul>
  <li><strong>AI Travel Concierge:</strong> Personalized multi-day itineraries, neighborhood safety breakdowns, and relocation guides in seconds.</li>
  <li><strong>Curated Destination Explorer:</strong> Discover hidden cafes, authentic street food, and iconic monuments powered by live Google Maps intelligence.</li>
  <li><strong>Vibrant Global Community:</strong> Share real-time travel journals, connect with fellow travelers, and exchange verified tips.</li>
</ul>

<h2>Join the Journey</h2>
<p>From Tokyo to Lisbon, Bali to Cape Town—Roots & Routes is your intelligent travel companion every step of the way.</p>',
                'status' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'status' => $page['status'],
                ]
            );
        }
    }
}
