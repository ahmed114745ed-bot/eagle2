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
        $data = [
            'top' => UserBadgeResource::collection(
                $userBadges->filter(fn($ub) => $ub->badge && $ub->badge->type === 'top')
            ),
            'regular' => UserBadgeResource::collection(
                $userBadges->filter(fn($ub) => $ub->badge && $ub->badge->type === 'regular')
            ),
        ];
        return Common::apiResponse(1, ' successfully',  $data, 200);
    }
}
