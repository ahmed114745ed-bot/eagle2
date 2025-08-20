<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\RankingService;
use App\Http\Controllers\Controller;
use Modules\Vip\Services\Api\VipService;
use App\Http\Resources\Api\V1\AgencyRankingRecourse;
use App\Http\Resources\Api\V1\NewAgencyRankingResource;

class RankingController extends Controller
{

    protected $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    public function ranking(Request $request)
    {
        Log::info("makled makled makled");
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;

        if (!in_array($class, [1, 2, 3, 4,5, 6]) || !in_array($type, [0, 1, 2, 3, 4])) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        $limit = $request->is_home ? 3 : 20;

        $data = $this->rankingService->getRanking($class, $type, $request->user(), $limit, $request->room_uid, $request->sent_to_owner);

        if ($class == 5) {
            $data =   AgencyRankingRecourse::collection($data);
        }

        return Common::apiResponse(1, '', $data);
    }

    public function ranking2(Request $request)
    {
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;

        if (!in_array($class, [1, 2, 3, 4,5, 6]) || !in_array($type, [0, 1, 2, 3, 4])) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        $limit = $request->is_home ? 3 : 10;

        $data = $this->rankingService->getRanking22($class, $type, $request->user(), $limit);

        if ($class == 5) {
            $data =   NewAgencyRankingResource::collection($data);
        }

        return Common::apiResponse(1, '', $data);
    }

    public function rankingV2(Request $request)
    {
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;

        if (!in_array($class, [1, 2, 3, 4, 6]) || !in_array($type, [0, 1, 2, 3, 4])) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        $limit = $request->is_home ? 3 : 20;

        $data = $this->rankingService->getRankingV2($class, $type, $request->user(), $limit, $request->room_uid, $request->sent_to_owner);

        return Common::apiResponse(1, '', $data);
    }

    public function topUserRanking()
    {
        $todayTopUsers = $this->rankingService->getTodayTopUsers();
        return Common::apiResponse(true, 'Success', $todayTopUsers);
    }

    public function oneRoomRanking(Request $request)
    {
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;

        if (!in_array($class, [1, 2,]) || !in_array($type, [0, 1, 2, 3, 4])) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        if (!$request->room_id) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        $limit = $request->is_home ? 3 : 20;
        $data = $this->rankingService->getRankingOneRoom($class, $type, $request->user(), $limit, $request->room_id, $request->sent_to_owner);
        return Common::apiResponse(1, '', $data);
    }
}
