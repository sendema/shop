<?php

return [
    'sandbox' => [
        'base_url' => env('PAYPAL_SANDBOX_BASE_URL', 'https://api-m.sandbox.paypal.com'),
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
    ],
    'live' => [
        'base_url' => env('PAYPAL_LIVE_BASE_URL', 'https://api-m.paypal.com'),
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
    ],
    'mode' => env('PAYPAL_MODE', 'sandbox')
];
