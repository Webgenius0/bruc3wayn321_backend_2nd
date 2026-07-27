<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'exchange_rate' => [
        'key' => env('EXCHANGE_RATE_API_KEY'),
    ],

    'revenuecat' => [
        'project_id' => env('REVENUECAT_PROJECT_ID'),
        'secret_api_key' => env('REVENUECAT_SECRET_API_KEY'),
    ],

    'openweather' => [
        'api_key' => env('OPENWEATHER_API_KEY'),
    ],
    // 'google' => [
    //     'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
    // ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'serpapi' => [
        'key' => env('SERPAPI_KEY'),
    ],

    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM'), // Default: Rachel
    ],

    'google_vision' => [
        'api_key' => env('GOOGLE_VISION_API_KEY'),
        'url' => 'https://vision.googleapis.com/v1/images:annotate',
    ],

    'google_translate' => [
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'url' => 'https://translation.googleapis.com/language/translate/v2',
    ],

    'google' => [
        'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => 'http://google.com/callback-url',
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => 'http://apple.com/callback-url',
    ],

    'firebase' => [
        'credentials_file' => env(
            'FIREBASE_CREDENTIALS_FILE',
            storage_path('app/public/firebase/rnrapp-76020-firebase-adminsdk-fbsvc-964eb06e93.json')
        ),
    ],
];
