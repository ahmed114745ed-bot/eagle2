<?php

namespace Utd\CP\Repositories;

use App\Models\GiftLog;
use App\Support\PackageHelper;
use Illuminate\Support\Facades\DB;
use Utd\CP\Entities\Cp;
use Utd\Events\Entities\GeneralRole;
use Utd\Events\Entities\WeeklyStar;

class WeeklyCpRepository
{
    public function currentWeeklyCp()
    {
        if (! PackageHelper::isInstalled('event')) {
            return null;
        }

        return WeeklyStar::currentEvent()->WeeklyCP()->with('gifts', 'weeklyCpGifts')->first();
    }

    public function perviousWeeklyCpTopWinner()
    {
        return $this->perviousWeeklyCp(['WeeklyCpWinners' => function ($query) {
            $query->where('type_relation', 'lovely')->where('level', 1);
        }]);
    }

    public function perviousWeeklyCp($withRelation = [])
    {
        if (! PackageHelper::isInstalled('event')) {
            return null;
        }

        return WeeklyStar::previousEvent()->WeeklyCP()->with($withRelation)->latest()->first();
    }

    public function perviousWeeklyCpWinners($limit = 3)
    {
        if (! PackageHelper::isInstalled('event')) {
            return collect([]);
        }

        return WeeklyStar::previousEvent()->WeeklyCP()->orderBy('start_date', 'desc')->with(['WeeklyCpWinners' => function ($query) {
            $query->where('type_relation', 'lovely')->with('userOne.profile', 'userTwo.profile');
        }])->limit($limit)->get();
    }

    public function role()
    {
        if (! PackageHelper::isInstalled('event')) {
            return null;
        }

        return GeneralRole::where('type', 'weekly_cp')->first();
    }

    public function topUsers($giftIds, $weeklyCp, int $perPage = 10)
    {
        $timezone = getTimezone();

        return GiftLog::whereIn('giftId', $giftIds)->select(DB::raw('sum(giftPrice) as totalGiftNum'), 'cp_id')
            ->groupBy('cp_id')
            ->whereBetween('created_at', [
                \Carbon\Carbon::parse($weeklyCp->start_date, $timezone)->startOfDay(),
                \Carbon\Carbon::parse($weeklyCp->end_date, $timezone)->endOfDay(),
            ])->whereHas('cps', function ($q) {
                $q->relation();
            })->with('cp')->orderByDesc('totalGiftNum')->paginate($perPage);
    }

    public function topUser($giftIds, $weeklyCp)
    {
        $timezone = getTimezone();

        return GiftLog::whereIn('giftId', $giftIds)
            ->select(DB::raw('sum(gift_logs.giftNum * gift_logs.giftPrice) as totalGiftNum'), 'cp_id')
            ->groupBy('cp_id')
            ->whereBetween('created_at', [
                \Carbon\Carbon::parse($weeklyCp->start_date, $timezone)->startOfDay(),
                \Carbon\Carbon::parse($weeklyCp->end_date, $timezone)->endOfDay(),
            ])->whereHas('cps', function ($q) {
                $q->relation();
            })->with('cp')->orderByDesc('totalGiftNum')->first();
    }

    public function userDetails($giftIds, $weeklyCp, $userId)
    {
        $cp = $this->userCP($userId);
        $priceGifts = GiftLog::whereIn('giftId', $giftIds)
            ->whereBetween('created_at', [$weeklyCp->start_date, $weeklyCp->end_date])
            ->where('cp_id', @$cp?->id)->sum('giftPrice');

        return $priceGifts;
    }

    public function userCP($userId)
    {
        return Cp::where(function ($query) use ($userId) {
            $query->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        })->relation()->orderByDesc('di')->first();
    }
}
