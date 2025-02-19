<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Database\Seeder;

class CountFollowSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::get();

        foreach ($users as $user) {
            $userId = $user->id;
            $followingCount = Follow::where("user_id", $user->id)->count();
            $followerCount = Follow::where("followed_user_id", $user->id)->count();
            $friendCount  = $user->numberOfFriends();

            $user->update([
                'following' => $user->following + $followingCount,
                'follower'  => $user->follower + $followerCount,
                'friend'     => $user->friend + $friendCount,
            ]);
        }
    }
}
