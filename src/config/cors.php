<?php

return [

    'paths' => [
        'api/*', 
        'sanctum/csrf-cookie',
        'broadcasting/auth', // <-- THÊM DÒNG NÀY VÀO
    ],

    'allowed_methods' => ['*'],
    
    // Cách làm của bạn rất tốt, giữ nguyên
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,
    
    'supports_credentials' => true,

];