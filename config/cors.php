<?php

return [

    'paths' => ['api/*', 'login', 'logout', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Untuk dev (Vite di :5173). Bisa spesifik: ['http://localhost:5173']
    'allowed_origins' => [
        'https://interactiveenglish.vercel.app',
        'https://interactiveenglish.netlify.app/',
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // true jika pakai cookie/Sanctum SPA. Jika pakai Bearer token, bisa false.
    'supports_credentials' => false,
];
