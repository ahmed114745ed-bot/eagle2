<?php

namespace Utd\RoomBoom\Http\Controllers\Api;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Utd\RoomBoom\Entities\SuperBoomRule;
use Utd\RoomBoom\Http\Resources\RoomBoomRuleResource;

class SuperBoomRuleController extends Controller
{
    public function index()
    {
        $data = SuperBoomRule::all();

        return Common::apiResponse(true, '', RoomBoomRuleResource::collection($data), 200);

    }
}
