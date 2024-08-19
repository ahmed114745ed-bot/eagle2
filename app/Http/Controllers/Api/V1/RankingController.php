<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Services\RankingService;
use App\Services\VipService;
use Illuminate\Http\Request;

class RankingController extends Controller
{

    protected $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    public function ranking(Request $request)
    {
        $class = $request->class ?: 1;
        $type = $request->type !== null ? $request->type : 1;
        
        if (!in_array($class, [1, 2, 3, 4]) || !in_array($type, [0, 1, 2, 3, 4])) {
            return Common::apiResponse(0, 'Parameter error', null, 422);
        }

        $limit = $request->is_home ? 3 : 20;

        $data = $this->rankingService->getRanking($class, $type, $request->user(), $limit, $request->room_uid, $request->sent_to_owner);

        return Common::apiResponse(1, '', $data);
    }

}
