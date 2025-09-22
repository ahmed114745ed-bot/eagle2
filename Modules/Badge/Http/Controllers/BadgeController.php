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

    public function index($userId)
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return Common::apiResponse(0, 'Invalid user ID', null, 400);
        }

        $userBadges = UserBadge::where('user_id', $userId)
            ->active()
            ->with(['badge' => function ($query) {
                $query->select('id', 'image', 'type', 'image_type');
            }])
            ->get();

        $filteredBadges = $userBadges->filter(function ($userBadge) {
            return !is_null($userBadge->badge);
        });

        $data = [
            'top' => UserBadgeResource::collection(
                $filteredBadges->where('badge.type', 'top')
            ),
            'regular' => UserBadgeResource::collection(
                $filteredBadges->where('badge.type', 'regular')
            ),
        ];

        return Common::apiResponse(1, 'User badges retrieved successfully', $data, 200);
    }
}
