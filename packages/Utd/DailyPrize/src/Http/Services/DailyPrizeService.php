<?php

namespace Utd\DailyPrize\Http\Services;

use App\Facades\RedisService;
use Carbon\Carbon;
use Utd\DailyPrize\Entities\DailyGift;
use Utd\DailyPrize\Entities\DailyGiftCount;
use Utd\DailyPrize\Transformers\WeeklyStarGift;

class DailyPrizeService
{

    public function reset(Carbon $lastActive, int $userId)
    {
        $lastActiveAddDay = $lastActive->addHours(24);

        if ($lastActiveAddDay < Carbon::now()->copy()->addMinutes(5)) {

            $dailyGift = $this->getUserDailyGiftData($userId);
            if ($dailyGift) {
                $dailyGift->day_count   = 0;
                $dailyGift->last_active = null;
                $dailyGift->save();
            }

            return true;
        }

        return false;
    }

    public function getUserDailyGiftData(int $userId): DailyGiftCount|null
    {
        return DailyGiftCount::query()->where('user_id', $userId)->first();
    }

    public function isNewDay(int $userId): bool
    {
        $dailyGift = $this->getUserDailyGiftData($userId);

        if (!$dailyGift || !$dailyGift->last_active) return true;
        $last_active      = Carbon::parse($dailyGift->last_active);
        $lastActiveAddDay = $last_active->copy()->addHours(24);
        return $lastActiveAddDay <= Carbon::now();
    }

    public function getDayGift(int $day)
    {
        $day = ($day % ($this->dailyGiftsCount() ?: 1)) ;
        if ($day == 0) $day = 7;
        return DailyGift::with('ware')->selectRaw('daily_gifts.*, (SELECT SUM((type - 1) * 7 + `order`) FROM daily_gifts as dg WHERE dg.id = daily_gifts.id) as day')
                        ->having('day', '=', $day)
                        ->first();
    }

    /**
     * @return int
     */
    public function dailyGiftsCount(): int
    {
        $count = RedisService::get('daily-gift-count');
        return intval($count ?? 28);
    }

    public function getWeekGifts(int $currentDay): array
    {
        $DAYS_IN_WEEK = 7;

        $currentWeek = intdiv($currentDay - 1, $DAYS_IN_WEEK);

        $startDay = 1 + ($currentWeek * $DAYS_IN_WEEK);
        $endDay = $startDay + $DAYS_IN_WEEK - 1;

        $gifts = [];

        for ($day = $startDay; $day <= $endDay; $day++) {
            $gifts[] = [
                'day'  => $day,
                'gift' => $this->getGift($day) ?? (object) [],
            ];
        }

        return $gifts;
    }

    public function getGift($currentDay)
    {
        $data = $this->getDayGift($currentDay);
        return  $data != null ? new WeeklyStarGift($data) : null;
    }

}
