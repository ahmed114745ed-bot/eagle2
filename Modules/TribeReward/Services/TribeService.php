<?php

namespace Modules\TribeReward\Services;

use App\Models\GiftLog;
use Modules\TribeReward\Entities\TribePeriod;

class TribeService
{
    public function index()
    {
        return TribePeriod::with('tribeTops.tribeRewards')->where('end_date', '<', now())
            ->orderBy('end_date', 'desc')
            ->firstOrFail();
    }

    public function agencyRanking()
    {
        $tribePeriod = TribePeriod::where('end_date', '<', now())->orderBy('end_date', 'desc')->firstOrFail();

        return GiftLog::selectRaw('agency_id, SUM(giftPrice) as total_exp')
            ->whereBetween('created_at', [$tribePeriod->start_date, $tribePeriod->end_date])
            ->whereNotNull('agency_id')
            ->whereHas('agency')
            ->where('agency_id', '!=', 0)
            ->groupBy('agency_id')
            ->orderByDesc('total_exp')
            ->get();
    }

}
