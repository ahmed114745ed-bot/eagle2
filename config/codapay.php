<?php

// return [
//     'base_url' => env('CODAPAY_BASE_URL', 'https://sandbox.codapayments.com/airtime/api/restful/v2.0/Payment/init.json'),
//     'api_key' => env('CODAPAY_API_KEY', 'test_kgaDbBSnvQZwiOGYulZfX561bae'),
//     'project_id' => env('CODAPAY_PROJECT_ID', 289),
//     'country' => env('CODAPAY_COUNTRY', 818),
//     'pay_type' => env('CODAPAY_PAY_TYPE', 338),
//     'currency' => env('CODAPAY_CURRENCY', 818),
// ];



// <?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Codapay Environment
    |--------------------------------------------------------------------------
    */
    'environment' => env('CODAPAY_ENV', 'sandbox'), // sandbox or production
    
    /*
    |--------------------------------------------------------------------------
    | Codapay API URLs
    |--------------------------------------------------------------------------
    */
    'urls' => [
        'sandbox' => 'https://sandbox.codapay.com',
        'production' => 'https://api.codapay.com',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Codapay Credentials
    |--------------------------------------------------------------------------
    */
    'api_key' => env('CODAPAY_API_KEY'),
    'project_id' => env('CODAPAY_PROJECT_ID'),
    
    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    'country' => env('CODAPAY_COUNTRY', '818'), // Egypt
    'currency' => env('CODAPAY_CURRENCY', '840'), // USD
    'pay_type' => env('CODAPAY_PAY_TYPE', 0),
    
    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    */
    'return_url' => env('APP_URL') . '/api/codapay-success',
    'fail_url' => env('APP_URL') . '/api/codapay-fail',
    
];




