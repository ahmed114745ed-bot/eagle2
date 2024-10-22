<?php

namespace Modules\CP\Repositories;

use App\Models\Cp;

use Carbon\Carbon;
use App\Models\GiftLog;
use Illuminate\Support\Facades\DB;
use Modules\CP\Entities\WeeklyCpWinner;
use Modules\Events\Entities\WeeklyStar;
use Modules\Events\Entities\GeneralRole;

/// todo remove rename import
class WeeklyCpRepository
{

    public function currentWeeklyCp()
    {
        return WeeklyStar::currentEvent()->WeeklyCP()->with('gifts', 'weeklyCpGifts')->first();
    }

    public function perviousWeeklyCp()
    {
        return WeeklyStar::previousEvent()->WeeklyCP()->latest()->first();
    }

    public function perviousWeeklyCpIds()
    {
        $weeklyCp =  $this->currentWeeklyCp();
        return WeeklyStar::where("start_date", '<', $weeklyCp->start_date)->pluck('id')->toArray();
    }

    public function perviousWeeklyCpWinners($typeRelation)
    {
        $perviousWeeklyCpId =    $this->perviousWeeklyCpIds();
        return WeeklyCpWinner::whereIn('weekly_cp_id', $perviousWeeklyCpId)
            ->with('userOne', 'userTwo', 'weeklyCp')
            ->where('type_relation', $typeRelation)
            ->get();
    }

    public function role()
    {
        return GeneralRole::where('type', 'weekly_cp')->first();
    }

    public function topUsers($giftIds, $weeklyCp)
    {
        $gifts = GiftLog::whereIn('giftId', $giftIds)->select(DB::raw('sum(giftPrice) as totalGiftNum'), 'cp_id')
            ->groupBy('cp_id')->whereBetween('created_at', [
                $weeklyCp->start_date,
                $weeklyCp->end_date
            ])->whereHas('cps', function ($q) {
                $q->relation();
            })->with('cp')->orderByDesc('totalGiftNum')->get();
        return $gifts;
    }

    public function firstPerviousWeeklyCp($perviousWeeklyCpId)
    {
        return WeeklyCpWinner::where('weekly_cp_id', $perviousWeeklyCpId)->where('level', 1)->first();
    }


    public function userDetails($giftIds, $weeklyCp, $userId)
    {
        $cp = $this->userCP($userId);
        $priceGifts =  GiftLog::whereIn('giftId', $giftIds)
            ->whereBetween('created_at', [$weeklyCp->start_date, $weeklyCp->end_date])
            ->where('cp_id', @$cp?->id)->sum("giftPrice");
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
