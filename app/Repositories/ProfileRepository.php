<?php

namespace App\Repositories;

use App\Models\Follow;
use App\Models\Profile;
use App\Models\User;
use App\Models\Vip;

class ProfileRepository
{
    protected $profile;
    protected $user;

    public function __construct(Profile $profile, User $user)
    {
        $this->profile = $profile;
        $this->user = $user;
    }

    public function updateUser($user, $data)
    {
        $user->fill($data);
        $user->is_points_first = 0;
        $user->save();
        return $user;
    }

    public function updateProfile($profile, $data)
    {
        $profile->fill($data);
        $profile->save();
        return $profile;
    }

    public function updateAvatar($profile, $imagePath)
    {
        $profile->avatar = $imagePath;
        $profile->save();
        return $profile;
    }
    
    public function getProfileVisits(User $user,$keyword)
    {
        return $user->profileVisits()->with([
            'room' => function ($query) {
                return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
            },
            'followPacks', 'profile', 'ware', 'UserVip'
        ])->fitterByUuid($keyword)->paginate(15);
    } 

    public function getRandomUsers($limit = 10)
    {
        $user = User::find(auth()->user()->id);
        return User::has("images")->whereNotIn("id",$user?->followeds?->pluck("followed_user_id")->toArray())->inRandomOrder()->where("online",1)->limit($limit)->get();
    }
   
}
