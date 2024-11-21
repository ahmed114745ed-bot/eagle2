<?php

namespace App\Repositories;

use App\Helpers\Common;
use App\Models\BlackList;
use App\Models\User;
use App\Models\Vip;
use Illuminate\Database\Eloquent\Model;

class BlackListRepository
{
    public function getUserBlackList($userId)
    {
        $black_list = Common::getUserBlackList ($userId);
        return User::query ()->whereIn ('id',$black_list)->get ();
    }

    public function removeUserFromBlackList($userId, $fromUserId)
    {
        return BlackList::query()
            ->where('user_id', $userId)
            ->where('from_uid', $fromUserId)
            ->delete();
    }

    public function addUserToBlackList($userId, $fromUserId)
    {
        return BlackList::create([
            'user_id' => $userId,
            'from_uid' => $fromUserId
        ]);
    }
}