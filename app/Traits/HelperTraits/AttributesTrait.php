<?php


namespace App\Traits\HelperTraits;


use App\Models\Pack;
use App\Models\Ware;

trait AttributesTrait
{

    public static function checkPack($userId, $type, $dress = null)
    {
        $pack = Pack::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where(function ($q) {
                $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
            });
        if ($dress != null) $pack->where('target_id', $dress);
        return $pack;
    }
    public static function getUserDress($user_id, $dress, $type, $item = 'img1', bool $isUsed = false)
    {

        $dr = '';
        if (!$dress) {
            return $dr;
        }
        $pack = self::checkPack($user_id, $type,$dress);

        if ($isUsed) {
            $pack->where('is_used', 1);
        }
        $pack = $pack->exists();
        if ($pack) {
            $ware = Ware::query()
                ->where('id', $dress)
                ->where('type', $type)
                ->first();

            if ($ware) {
                $dr = $ware->{$item};
            }
        }

        return $dr ?: '';
    }

    public static function pack_get($key, $user_id)
    {
        return Pack::query()
            ->where('user_id', $user_id)
            ->where('type', $key)
            ->where(function ($q) {
                $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
            })
            ->exists();
    }
}
