<?php

namespace Modules\TribeReward\Services;

use App\Helpers\UserCommon;
use App\Models\GiftLog;
use App\Models\OVip;
use App\Models\User;
use App\Models\Ware;
use Carbon\Carbon;
use Exception;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\TribeReward\Entities\AgencyReward;
use Modules\TribeReward\Entities\TribePeriod;

class TribeService
{
    public function index()
    {
        return TribePeriod::with('tribeTops.tribeRewards')->where('end_date', '<', now())
            ->orderBy('end_date', 'desc')
            ->firstOrFail();
    }
}
