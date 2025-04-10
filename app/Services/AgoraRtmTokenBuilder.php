<?php

namespace App\Services;
use DateTime;
use DateTimeZone;
class AgoraRtmTokenBuilder
{
    const RoleRtmUser = 1; // Role for RTM user

    public static function generateRtmToken(string $userId, int $expireSeconds = 3600): string
    {
        $appId = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');

        if (empty($appId) || empty($appCertificate)) {
            throw new \RuntimeException('Agora App ID and Certificate must be configured');
        }

        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireSeconds;

        return self::buildToken($appId, $appCertificate, $userId, self::RoleRtmUser, $privilegeExpiredTs);
    }

    private static function buildToken($appId, $appCertificate, $user, $role, $privilegeExpiredTs)
    {
        $token = [
            'app_id' => $appId,
            'user_id' => $user,
            'role' => $role,
            'issue_ts' => time(),
            'expire_ts' => $privilegeExpiredTs,
            'salt' => random_int(1, 99999999),
            'privileges' => [
                'login' => $privilegeExpiredTs
            ]
        ];

        $signature = hash_hmac('sha256', json_encode($token), $appCertificate);
        
        return base64_encode(json_encode([
            'token' => $token,
            'signature' => $signature
        ]));
    }
}