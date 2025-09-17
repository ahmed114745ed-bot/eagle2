<?php

namespace Modules\Badge\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Badge\Entities\UserBadge;
use Illuminate\Contracts\Support\Renderable;
use Modules\Badge\Http\Resources\UserBadgeResource;

class BadgeController extends Controller
{

    public function index($id)
    {
        $userBadges = UserBadge::where('user_id', $id)->active()->with("badge")->get();
        return Common::apiResponse(1, ' successfully', UserBadgeResource::collection($userBadges), 200);
    }
}
