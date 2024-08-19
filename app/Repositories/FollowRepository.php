<?php

namespace App\Repositories;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FollowRepository
{
    public function findFollow($userId, $followedUserId)
    {
        return Follow::where('user_id', $userId)
                      ->where('followed_user_id', $followedUserId)
                      ->first();
    }

    public function createFollow($data)
    {
        return Follow::create($data);
    }

    public function updateFollowStatus($follow, $status)
    {
        $follow->status = $status;
        $follow->save();
    }

    public function deleteFollow($userId, $followedUserId)
    {
        return Follow::where('user_id', $userId)
                     ->where('followed_user_id', $followedUserId)
                     ->delete();
    }

    public function findUserById($userId)
    {
        return User::find($userId);
    }
}