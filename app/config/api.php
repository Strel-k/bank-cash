<?php
// Global API configuration
return [
    'base_url' => '/public/api',  // Updated to match frontend URL
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://localhost:4000',
        'http://127.0.0.1:4000',
        'http://localhost:5000',
        'http://127.0.0.1:5000'
    ],
    'cookie_domain' => '',
    'use_secure_cookies' => false  // Set to true in production with HTTPS
];
