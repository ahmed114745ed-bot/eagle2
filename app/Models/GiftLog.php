<?php

namespace App\Models;

use App\Traits\HostLevelTrait;
use Modules\CP\Traits\CpGiftLog;
use Modules\Moment\Entities\Moment;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class GiftLog extends Model
{
    use TimestampsWithTimezone, CpGiftLog, HostLevelTrait;

    protected $table = 'gift_logs';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // Clear gift rankings cache when gift_logs are created or deleted
        static::created(function () {
            self::clearGiftRankingsCache();
        });

        static::deleted(function () {
            self::clearGiftRankingsCache();
        });
    }

    /**
     * Clear all gift rankings cache
     * Covers both RankingRepository and RankingRepositoryV2
     */
    protected static function clearGiftRankingsCache(): void
    {
        // Clear all cached gift ranking variations from both repositories
        $patterns = [
            'gift_rankings_*',      // RankingRepository & RankingRepositoryV2
            'gift_rankings_v2_*',   // RankingRepository
            'room_ranking_*'        // GiftLogRepository
        ];

        try {
            $redis = Cache::getStore()->getRedis();
            $prefix = config('database.redis.options.prefix', '');

            foreach ($patterns as $pattern) {
                $keys = $redis->keys($prefix . $pattern);
                if (!empty($keys)) {
                    foreach ($keys as $key) {
                        $cleanKey = str_replace($prefix, '', $key);
                        Cache::forget($cleanKey);
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback: just clear known cache keys without pattern matching
            \Log::warning('Failed to clear gift rankings cache with pattern: ' . $e->getMessage());
        }
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'giftId', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function roomOwner()
    {
        return $this->belongsTo(User::class, 'roomowner_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class, 'moent_id');
    }



    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }
}
