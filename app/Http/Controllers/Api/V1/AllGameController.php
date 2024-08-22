<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Services\AllGameService;
use App\Helpers\Common;
use Illuminate\Http\Request;

class AllGameController extends Controller
{
    protected $allGameService;

    public function __construct(AllGameService $allGameService)
    {
        $this->allGameService = $allGameService;
    }

    public function index()
    {
        $data = $this->allGameService->getAllGamesData();
        return Common::apiResponse(1, '', $data);
    }

    public function updateGame(Request $request)
    {
        $result = $this->allGameService->updateGame($request->game_id, $request->user());
        return Common::apiResponse($result['status'], $result['message'], $result['code']);
    }
}
