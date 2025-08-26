<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL', 'https://admin.noteurgoals.live') . '/api/auth/google/callback-direct',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('APP_URL', 'https://admin.noteurgoals.live') . '/api/auth/facebook/callback-direct',
    ],

    'ai_microservice' => [
        'url' => env('AI_MICROSERVICE_URL', 'https://admin.noteurgoals.live'),
        'timeout' => env('AI_MICROSERVICE_TIMEOUT', 30),
        'api_key' => env('AI_MICROSERVICE_API_KEY'),
        'enabled' => env('AI_MICROSERVICE_ENABLED', true),
        'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),
        'cache_ttl' => env('AI_CACHE_TTL', 3600), // 1 hour
    ],
];