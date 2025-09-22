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

    public function index(int $userId)
    {
        if ($userId <= 0) {
            return Common::apiResponse(0, 'Invalid user ID', null, 400);
        }

        $userBadges = UserBadge::query()
            ->where('user_id', $userId)
            ->active()
            ->with(['badge:id,image,type,image_type'])
            ->get()
            ->filter(fn($userBadge) => $userBadge->badge !== null);

        $data = [
            'top' => UserBadgeResource::collection(
                $userBadges->where('badge.type', 'top')
            ),
            'regular' => UserBadgeResource::collection(
                $userBadges->where('badge.type', 'regular')
            ),
        ];

        return Common::apiResponse(1, 'User badges retrieved successfully', $data,  200);
    }
}
