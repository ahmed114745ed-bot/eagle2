<?php

namespace Utd\RoomBoom\Http\Controllers\Api;

use App\Helpers\Common;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Utd\RoomBoom\Services\RoomBoomLevelService;
use Utd\RoomBoom\Transformers\RoomBoomLevelResource;

class RoomBoomLevelController extends Controller
{
    public function __construct(private readonly RoomBoomLevelService $roomBoomLevelService)
    {
    }

    public function index($id): JsonResponse
    {
        $roomBoomLevels = $this->roomBoomLevelService->index($id);

        return Common::apiResponse(true, '', RoomBoomLevelResource::collection($roomBoomLevels), 200);
    }

    public function getVideos(): JsonResponse
    {
        $roomBoomLevels = $this->roomBoomLevelService->getVideos();

        return Common::apiResponse(true, '', RoomBoomLevelResource::collection($roomBoomLevels), 200);
    }
}
