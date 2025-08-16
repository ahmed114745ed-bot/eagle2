<?php

namespace App\Helpers;

use App\Models\User;

class UserPackHelper
{

    public static function getColorName(User $user) : string
    {
        return $user->packs
            ->where('type', 18)
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

    public static function getVipIcon(User $user) : string
    {
        return $user->packs
            ->where('type', 10)
            ->first()?->ware?->show_img ?? '';
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
