<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Services\RankingService;
use Illuminate\Http\Request;

class Ranking2Controller extends Controller
{
    protected $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }


    public function ranking(Request $request){
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;

        if (!in_array($class, [1, 2, 3, 4]) || !in_array($type, [0, 1, 2, 3, 4])) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        $limit = $request->is_home ? 3 : 20;

        $data = $this->rankingService->getRanking2($class, $type, $request->user(), $limit, $request->room_uid, $request->sent_to_owner);

        return Common::apiResponse(1, '', $data);
    }
    public function ranking_room(Request $request) {
        $type     = $request->input('type', 2);
        $room_uid = $request->input('room_uid');
        $limit    = $request->input('is_home') ? 3 : 30;
        $user_id  = $request->user()->id;

        $data = $this->rankingService->getRoomRanking($room_uid, $type, $limit, $user_id);

        return Common::apiResponse(1, '', $data);
    }


    public function topUserRanking()
    {
        return $this->rankingService->topUser2();
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
        $data = $this->rankingService->getRankingOneRoom2($class, $type, $request->user(), $limit, $request->room_id, $request->sent_to_owner);
        return Common::apiResponse(1, '', $data);
    }
}
