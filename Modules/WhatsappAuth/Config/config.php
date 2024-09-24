<?php

return [
    'name' => 'WhatsappAuth',
    'server_url' => env('WHATSAPP_SERVER_URL', 'https://tik-chat.com/api/verification_code_service'),
    'server_url_login' => env('WHATSAPP_SERVER_URL_LOGIN', 'https://tik-chat.com/api/server/auth/login'),
    'username' => env('WHATSAPP_USERNAME'),
    'password' => env('WHATSAPP_PASSWORD'),
    'whatsapp_token' => env('WHATSAPP_TOKEN'),
    'base_url' => env('WHATSAPP_BASE_URL','http://127.0.0.1:8001'),
];
