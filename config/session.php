<?php

return [

    'driver' => env('SESSION_DRIVER', 'file'),

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => 'sessions',

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => [
        'name' => env('SESSION_COOKIE', 'XSRF-TOKEN'),
        'path' => env('SESSION_COOKIE_PATH', '/'),
        'domain' => env('SESSION_COOKIE_DOMAIN'),
        'secure' => env('SESSION_COOKIE_SECURE', false),
        'http_only' => env('SESSION_COOKIE_HTTP_ONLY', true),
        'same_site' => env('SESSION_COOKIE_SAME_SITE', 'lax'),
    ],

];
