<?php

namespace Modules\Whatsapp\Services;

use Database\Seeders\config;
use Illuminate\Support\Facades\Http;

class WhatsappOTPService
{

    public function sendMessage($phone, $code, $phoneId = null, string $local = 'en'): bool
    {

        if (!in_array($local, ['ar', 'en'])) $local = 'en';

        $phoneId = $phoneId ?? config('whatsapp.phone_id');

        $token = (string)config('whatsapp.whatsapp_token');
        $url = 'https://graph.facebook.com/v19.0/' . $phoneId . '/messages';



        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'template',
                'template' => [
                    'name' => 'verification',
                    'language' => [
                        'code' => $local
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $code
                                ]
                            ]
                        ],
                        [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => 0,
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $code // Ensure this is a valid URL
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $data = $response->json();
        if (array_key_exists('error', $data)){
            return false;
            dd('error');
        }
        return true;

    }

}
