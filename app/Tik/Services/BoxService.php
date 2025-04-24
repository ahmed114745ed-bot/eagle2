<?php

namespace App\Tik\Services;

use App\Models\Pack;
use App\Models\UserVip;
use App\Tik\Repositories\BlackLisRepository;
use Modules\Events\Entities\WinnerReward;
use App\Http\Resources\UserReportResource;
use App\Http\Resources\ReportEventResource;
use Modules\Events\Entities\RewardWinnerPk;
use App\Http\Resources\AgencyReportResource;
use Modules\Events\Services\LoseWinnerRewards;
use App\Http\Resources\AdminUserReportResource;
use Modules\Achievement\Entities\UserAchievementLevel;

class BoxService
{
    public function __construct(
        private readonly BlackLisRepository $Repository,
   
    ) {}
}