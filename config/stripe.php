<?php



return [
    'test_secret_key' => env('STRIPE_TEST_SECRET_KEY'),
    'success_url' => env('STRIPE_SUCCESS_URL', 'https://www.google.com'),
    'cancel_url' => env('STRIPE_CANCEL_URL', 'https://www.google.com'),
    'currency' => env('STRIPE_CURRENCY', 'usd'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'. '')
];
