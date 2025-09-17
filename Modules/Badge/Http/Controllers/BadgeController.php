<?php

namespace Modules\Badge\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Badge\Entities\UserBadge;
use Illuminate\Contracts\Support\Renderable;

class BadgeController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $userBadges = UserBadge::where('user_id', $userId)->active()->with("badge")->get();
        return Common::apiResponse(1, ' successfully', $userBadges, 200);
    }
}
