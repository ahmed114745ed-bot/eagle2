<?php

namespace Modules\RoomBoom\Http\Controllers\Api;

use App\Helpers\Common;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\RoomBoom\Services\RoomBoomLevelService;
use Modules\RoomBoom\Transformers\RoomBoomLevelResource;

class RoomBoomLevelController extends Controller
{
    public function __construct(private readonly RoomBoomLevelService $roomBoomLevelService)
    {
    }

    public function index(): JsonResponse
    {
        $roomBoomLevels = $this->roomBoomLevelService->index();

        return Common::apiResponse(true, '', RoomBoomLevelResource::collection($roomBoomLevels), 200);
    }
}
