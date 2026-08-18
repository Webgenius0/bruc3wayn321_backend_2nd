<?php

return [
    'free_trial_days' => 7,
    'free_trial_enabled' => true,
    'freemium_enabled' => true,
    'tiers' => [
        'free' => [
            'ai_chat_messages' => 10,
            'ai_voice_requests' => 0,
            'community_posts' => 3,
        ],
        'paid' => [
            'ai_chat_messages' => 1000,
            'ai_voice_requests' => 200,
            'community_posts' => 99999,
        ],
    ],
];