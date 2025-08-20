<?php

return [
    'base_url'      =>  'https://api-m.sandbox.paypal.com',
    'mode'          =>  'sandbox',
    'client_id'     => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'currency'      => env('PAYPAL_CURRENCY', 'GBP'),
    'webhook_id'      => env('PAYPAL_WEBHOOK_ID', '123'),
    ];



    // 'base_url'      => env('PAYPAL_MODE', 'sandbox') === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com',
    // 'mode'          => env('PAYPAL_MODE', 'sandbox'),
