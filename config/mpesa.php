<?php

return [
    'env' => env('MPESA_ENV', 'sandbox'),

    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),

    'shortcode' => env('MPESA_SHORTCODE', '174379'),
    'passkey' => env('MPESA_PASSKEY', ''),

    'callback_url' => env('MPESA_CALLBACK_URL', ''),

    // Sandbox URLs
    'sandbox' => [
        'auth_url' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        'stk_url' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
        'query_url' => 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query',
    ],

    // Production URLs
    'live' => [
        'auth_url' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        'stk_url' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
        'query_url' => 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query',
    ],
];
