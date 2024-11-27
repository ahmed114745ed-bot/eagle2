<?php



return [
    'test_secret_key' => env('STRIPE_TEST_SECRET_KEY'),
    'success_url' => env('STRIPE_SUCCESS_URL'),
    'cancel_url' => env('STRIPE_CANCEL_URL'),
    'currency' => env('STRIPE_CURRENCY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
