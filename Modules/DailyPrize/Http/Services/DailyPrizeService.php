<?php

namespace Modules\DailyPrize\Http\Services;

use App\Facades\RedisService;
use Carbon\Carbon;
use Modules\DailyPrize\Entities\DailyGift;
use Modules\DailyPrize\Entities\DailyGiftCount;

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
      //  \Log::info('this test daily '.$this->dailyGiftsCount());
        $day = $day % ($this->dailyGiftsCount() ?: 1);
        return DailyGift::with('ware')->selectRaw('daily_gifts.*, (SELECT SUM((type - 1) * 7 + `order`) FROM daily_gifts as dg WHERE dg.id = daily_gifts.id) as day')
                        ->having('day', '=', $day)
                        ->first();
    }


//    public function getUserDailyGiftData(int $userId): DailyGiftCount|null
//    {
//        return DailyGiftCount::query()->where('user_id', $userId)->first()
//        ->having('day', '=', $day)
//        ->first();
//    }
    /**
     * @return int
     */
    public function dailyGiftsCount(): int
    {
        $count = RedisService::get('daily-gift-count');
        return intval($count ?? 28);
    }

}
