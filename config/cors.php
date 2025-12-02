<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Izinkan SEMUA PATH, termasuk OPTIONS yang dikirim browser
    'paths' => ['*'],

    // Semua method HTTP boleh
    'allowed_methods' => ['*'],

    // Daftar origin FE yang boleh akses API kamu
    'allowed_origins' => [
        'https://lexentenglish.vercel.app',     // FE Production Vercel
        'http://localhost:5173',                // FE Dev mode (Vite)
    ],

    // Tidak perlu patterns
    'allowed_origins_patterns' => [],

    // Semua header boleh
    'allowed_headers' => ['*'],

    // Header apa yang diekspose ke FE
    'exposed_headers' => [],

    'max_age' => 0,

    // Kalau tidak pakai SESSION / COOKIE Sanctum, TRUE tidak perlu
    // Kamu pakai Bearer Token → set FALSE
    'supports_credentials' => false,
];
