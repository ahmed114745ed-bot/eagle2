<?php

return [
    'name' => 'Whatsapp',
    'app_name' => env('APP_NAME'),
    'phone' => env('WHATSAPP_PHONE'),
    'phone_id' => env('PHONE_ID'),
    'whatsapp_token' => env('WHATSAPP_TOKEN'),
    'server_url' => env('WHATSAPP_SERVER_URL', 'https://tik-chat.com/api/verification_code_service'),
    'server_url_login' => env('WHATSAPP_SERVER_URL_LOGIN', 'https://tik-chat.com/api/server/auth/login'),
    'username' => env('WHATSAPP_USERNAME'),
    'password' => env('WHATSAPP_PASSWORD'),
];
