<?php

namespace App\helper;

use App\Http\Resources\Api\V1\MiniUserResource;
use App\Http\Resources\Api\V1\NowRoomResource;
use App\Http\Resources\UserDataRoomResource;
use Carbon\Carbon;
use App\Models\User;

class UserDataHelper
{
    public static function formatAgency(User $user): ?array
    {
        if (!$user->agency) return null;

        $owner = $user->agency->app_owner_id == $user->id
            ? new \stdClass()
            : new MiniUserResource($user->agency->owner);

        return [
            'id'           => $user->agency->id,
            'name'         => $user->agency->name,
            'status'       => $user->agency->status,
            'image'        => $user->agency->img,
            'member_count' => $user->agency->members?->count() ?? 0,
            'owner'        => $owner,
        ];
    }

    public static function formatFamily(User $user): ?array
    {
        if (!$user->family) return null;

        return [
            'owner_id'       => $user->family->user_id,
            'family_name'    => $user->family->name,
            'img'            => $user->family->image,
            'num_of_members' => $user->family->members?->count() ?? 0,
        ];
    }

    public static function formatOnlineTime(User $user): string
    {
        if (!$user->online_time) return '';

        $onlineTime = Carbon::createFromTimestamp($user->online_time);

        return $onlineTime->isPast()
            ? $onlineTime->diffForHumans(now())
            : 'In the future';
    }

    public static function formatNowRoom(User $user)
    {
        if (!$user->now_room_uid) {
            return [];
        }
    
        if ($user->relationLoaded('nowRoomOwner')) {
            $nowRoomOwner = $user->nowRoomOwner;
        } else {
            $nowRoomOwner = $user->loadMissing([
                'nowRoomOwner.packs' => fn($q) => $q->where('is_used', 1)->with('ware'),
            ])->nowRoomOwner;
        }
    
        if ($nowRoomOwner?->getPackWithTypeV3(16)) {
            return (object)[];
        }
    
        return new NowRoomResource($user);
    }
    
    public static function formatShippingAgency(User $user): ?array
    {
        if (!$user->shippingAgency) return null;

        return [
            "id"                    => $user->shippingAgency->id,
            "name"                  => $user->shippingAgency->name ?? '',
            "image"                 => $user->shippingAgency->img ?? '',
            "complete-transactions" => $user->shippingAgency->charges?->count() ?? 0,
        ];
    }

    public static function getUserDress(User $user, $type, $dress, $item = 'img1'): string
    {
        $pack = $user->packs
            ->where('type', $type)
            ->where('target_id', $dress)
            ->first();

        return $pack?->ware?->{$item} ?? '';
    }
}
