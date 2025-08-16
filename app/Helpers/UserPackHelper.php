<?php

namespace App\Helpers;

use App\Models\User;

class UserPackHelper
{

    public static function getColorName(User $user) : string
    {
        LogHelper::info('User info ' , $user);
        return $user->packs
            ->where('type', 18)
            ->where('is_used', true)
            ->first()?->ware?->color ?? '';
    }

    public static function getFrameImage(User $user) : string
    {
        $ware = self::getFrameWare($user);
        return $ware?->img2 ?? ($ware?->img1 ?? '');
    }

    public static function getFrameId(User $user) : string
    {
        $ware = self::getFrameWare($user);
        return $ware?->id ?? 0;
    }

    /**
     * @param User $user
     * @return \App\Models\Ware|mixed|null
     */
    public static function getFrameWare(User $user): mixed
    {
        return $user->packs
            ->where('type', 4)
            ->where('is_used', true)
            ->first()?->ware;
    }
}
