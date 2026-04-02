<?php

namespace App\Services\FairLuck\V7;

use Illuminate\Support\Facades\Redis;

class UserRTPTracker
{
    private const KEY_PREFIX = 'fairluck:V7:user:';

    public function getStats(int $userId): object
    {
        // TTL PROTECTION: Validate and reset expired data
        $ttlManager = app(UserDataTTLManager::class);
        if (!$ttlManager->validateUserData($userId)) {
            // Data was expired and reset, return fresh stats
            return (object) [
                'total_spent' => 0.0,
                'total_received' => 0.0,
                'bet_count' => 0,
                'win_count' => 0,
                'first_bet_ts' => 0,
            ];
        }

        $key = self::KEY_PREFIX . $userId;
        $data = Redis::hgetall($key);

        return (object) [
            'total_spent' => (float) ($data['total_spent'] ?? 0),
            'total_received' => (float) ($data['total_received'] ?? 0),
            'bet_count' => (int) ($data['bet_count'] ?? 0),
            'win_count' => (int) ($data['win_count'] ?? 0),
            'first_bet_ts' => (int) ($data['first_bet_ts'] ?? 0),
        ];
    }

    public function getRTP(int $userId): float
    {
        $stats = $this->getStats($userId);
        if ($stats->total_spent <= 0) {
            return 0.0;
        }
        return $stats->total_received / $stats->total_spent;
    }

    public function recordBet(int $userId, float $spent, float $received, bool $isWinner): void
    {
        $key = self::KEY_PREFIX . $userId;

        Redis::hincrbyfloat($key, 'total_spent', $spent);
        if ($received > 0) {
            Redis::hincrbyfloat($key, 'total_received', $received);
        }
        Redis::hincrby($key, 'bet_count', 1);
        if ($isWinner) {
            Redis::hincrby($key, 'win_count', 1);
        }

        if (!Redis::hexists($key, 'first_bet_ts')) {
            Redis::hset($key, 'first_bet_ts', time());
        }

        // TTL PROTECTION: Record activity timestamp
        $ttlManager = app(UserDataTTLManager::class);
        $ttlManager->recordActivity($userId);
    }
}
