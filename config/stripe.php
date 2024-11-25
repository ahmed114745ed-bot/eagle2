<?php



return [
    'test_secret_key' => env('STRIPE_TEST_SECRET_KEY',"sk_test_51QDK2SATuMaocXXJZgv1YsOhf1AGmXyzoeUQ9b65xmMj84EVTvPHiljxwQdMoXTJoop4Y2F8wzSSdjP9EI1Z9ehF00V3zCFXSh"),
    'success_url' => env('STRIPE_SUCCESS_URL', 'https://www.google.com'),
    'cancel_url' => env('STRIPE_CANCEL_URL', 'https://www.google.com'),
    'currency' => env('STRIPE_CURRENCY', 'usd'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'. 'whsec_2PTszrAQTltl0FksfIytAfSyQMx3dQqq')
];
