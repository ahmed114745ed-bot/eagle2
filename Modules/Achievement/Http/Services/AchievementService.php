<?php

namespace Modules\Achievement\Http\Services;

use App\Models\User;
use Modules\Achievement\Entities\Achievement;

class AchievementService
{
    public function show(User $user, int $page = 1)
    {
        $userId = $user->id;
        return Achievement::query()
                          /*->withExists(['userAchievement' => function($query) use($userId){
                              $query->where('user_id', $userId)->where('is_achieve', true);
                          }])*/
                          ->get();
    }

    public function create(array $data)
    {

    }

}
