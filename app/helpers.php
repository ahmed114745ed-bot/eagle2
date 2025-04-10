<?php

use BoogieFromZk\AgoraToken\RtcTokenBuilder2;
use \Firebase\JWT\JWT;

if (!function_exists('agency_base_path')) {
    /**
     * Get admin url.
     *
     * @param string $path
     *
     * @return string
     */
    function agency_base_path($path = '')
    {
        $prefix = '/'.trim(config('agency.route.prefix'), '/');

        $prefix = ($prefix == '/') ? '' : $prefix;

        $path = trim($path, '/');

        if (is_null($path) || strlen($path) == 0) {
            return $prefix ?: '/';
        }

        return $prefix.'/'.$path;
    }

    function getRtcToken($uid, $channelName){

        $appId = env("AGORA_APP_ID");
        // Need to set environment variable AGORA_APP_CERTIFICATE
        $appCertificate = env("AGORA_APP_CERTIFICATE");

        $tokenExpirationInSeconds = 3600;
        $privilegeExpirationInSeconds = 3600;

        if ($appId == "" || $appCertificate == "") {
            echo "Need to set environment variable AGORA_APP_ID and AGORA_APP_CERTIFICATE" . PHP_EOL;
            return;
        }

        $token = RtcTokenBuilder2::buildTokenWithUid($appId, $appCertificate, $channelName, $uid, RtcTokenBuilder2::ROLE_PUBLISHER, $tokenExpirationInSeconds, $privilegeExpirationInSeconds);
        return $token;
    }


    function generateRtmToken($userId, $expireTimeInSeconds = 3600) {
        $currentTime = time();
    
        
        $payload = [
            "app_id" => env('AGORA_APP_ID'),
            "user_id" => 'user-' . 237,
            "iat" => $currentTime,
            "exp" => $currentTime + $expireTimeInSeconds,
        ];

        return JWT::encode($payload, env('AGORA_APP_CERTIFICATE'), 'HS256');
    }
    function sendMessageToChannel()
    {
        $appId = 'f00c471d45534266833097246509e475';
        $customerId = '7a1ee28358244419a49a1b4795ea40c6';
        $customerSecret = '5b499d4e81564bde915f4f9419a903fb';
        $channelName = 'my_channel_123';
        $message = 'hi';

        // Generate base64-encoded authorization
        $auth = base64_encode("$customerId:$customerSecret");

        // Send message using Agora REST API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-agora-token' => $auth,
        ])->post("https://api.agora.io/dev/v2/project/" . $appId . "/rtm/users/2/channel_messages", [
            'channel' => $channelName,
            'message' => [
                'text' => $message,
            ],
        ]);

        if ($response->successful()) {
            return response()->json(['status' => 'success', 'message' => 'Message sent']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Failed to send message'], 500);
        }
    }
}
