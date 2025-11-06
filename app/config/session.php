<?php
// Session configuration
return [
    'cookie_lifetime' => 86400, // 24 hours
    'cookie_path' => '/',
    'cookie_domain' => '',
    'cookie_secure' => false, // Set to true in production with HTTPS
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
    'gc_maxlifetime' => 86400,
    'name' => 'BCASH_SESSION'
];
