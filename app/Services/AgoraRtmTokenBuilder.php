<?php

namespace App\Services;

class AgoraRtmTokenBuilder
{
    public static function buildToken($appId, $appCertificate, $userId, $expireTimestamp)
    {
        $version = "1";
        $content = self::packString($userId);
        $content .= self::packUint32($expireTimestamp);
        
        $signature = hash_hmac('sha256', $content, hex2bin($appCertificate), true);
        $signature = substr($signature, 0, 20);
        
        $token = self::packString($signature);
        $token .= $content;
        
        return $version . base64_encode($token);
    }

    private static function packString($value)
    {
        return pack('v', strlen($value)) . $value;
    }

    private static function packUint32($value)
    {
        return pack('V', $value);
    }
}