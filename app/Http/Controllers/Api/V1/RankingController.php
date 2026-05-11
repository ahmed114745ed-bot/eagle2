<?php

namespace App\Http\Controllers\Api\V1;

use App\helper\TryCatchHelper;
use App\Helpers\Common;
use App\helper\RankingHelper;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Services\RankingServiceV2;
use App\Services\RankingService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Mockery\Exception;
use Modules\Vip\Services\Api\VipService;
use App\Http\Resources\Api\V1\AgencyRankingRecourse;
use App\Http\Resources\Api\V1\NewAgencyRankingResource;

class RankingController extends Controller
{

    protected $rankingService;
    protected $rankingServiceV2;
    protected $userService;

    public function __construct(RankingService $rankingService, RankingServiceV2 $rankingServiceV2, UserService $userService)
    {
        $this->rankingService = $rankingService;
        $this->rankingServiceV2 = $rankingServiceV2;
        $this->userService = $userService;

    }

    public function ranking(Request $request)
    {
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;

        if (!in_array($class, [1, 2, 3, 4, 5, 6]) || !in_array($type, [0, 1, 2, 3, 4])) {
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

        // return Common::apiResponse(0, __('closed for update'));

        // throw new Exception(__('closed for update'));

        return TryCatchHelper::handle(function () use ($request) {

            $class = (int) ($request->class ?? 1);
            $type  = (int) ($request->type ?? 1);

            if ($error = RankingHelper::validateParams($class, $type)) {
                return $error;
            }

            $limit = RankingHelper::getLimit((bool) $request->is_home);

            $data = $this->rankingService->getRanking22($class, $type, $request->user(), $limit);

            $data = RankingHelper::transformData($class, $data);

            return $data;
        });
    }

    public function ranking3(Request $request)
    {
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;

        if (!in_array($class, [1, 2, 3, 4, 5, 6]) || !in_array($type, [0, 1, 2, 3, 4])) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        $limit = $request->is_home ? 3 : 10;

        $data = $this->rankingService->getRanking66($class, $type, $request->user(), $limit);

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
        // Performance fix: cache ranking results for 5 minutes to reduce DB pressure
        // Rankings don't need to be real-time — prevents duplicate heavy queries
        $todayTopUsers = Cache::remember('top_user_ranking', 300, function () {
            return $this->rankingService->getTodayTopUsers();
        });
        return Common::apiResponse(true, 'Success', $todayTopUsers);
    }

    public function oneRoomRanking(Request $request)
    {
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;

        if (!in_array($class, [1, 2,]) || !in_array($type, [0, 1, 2, 3, 4])) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        if (!$request->room_id && !$request->roomId) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        $limit = $request->is_home ? 3 : 20;

        if ($request->roomId) {
            $data = $this->rankingServiceV2->getRankingOneRoom($class, $type, $request->user(), $limit, $request->roomId, $request->sent_to_owner);
        }

        if ($request->room_id ) {
            $data = $this->rankingService->getRankingOneRoom($class, $type, $request->user(), $limit, $request->room_id, $request->sent_to_owner );
        }
        return Common::apiResponse(1, '', $data);
    }
}
