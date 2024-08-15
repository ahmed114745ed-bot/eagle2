<?php

namespace App\Repositories;

use App\Models\Profile;
use App\Models\User;

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
    
}
