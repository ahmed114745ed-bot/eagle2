<?php

namespace Modules\Vip\Helpers;

use App\Models\Pack;
use App\Models\UserVip;
use App\Models\Ware;

class VipCommon
{

    public static function handelVip($vip, $user, $expire, $userVip)
    {
        if ($userVip->is_used) {
            $types = $vip->privilegs()->pluck('type')->filter()->unique()->toArray();
    
            Pack::query()
                ->where('get_type', 1)
                ->where('user_id', $user->id)
                ->whereIn('type', $types)
                ->where('vip_user_id', '!=', $userVip->id)
                ->update(['is_used' => 0]);
        }
    
        $types = $vip->privilegs()->pluck('type')->toArray();
    
        foreach ($types as $type) {
            $ware = Ware::where('get_type', 1)
                ->where('level', $vip->level)
                ->where('type', $type)
                ->first();
    
            if ($ware) {
                $ware->update([
                    'is_active_for_vip' => 1,
                    'enable' => 1,
                ]);
            }
        }
    
        $expireDays = $expire ?? $vip->expire;
        $expireTimestamp = now()->addDays($expireDays)->timestamp;
    
        $wares = Ware::where('get_type', 1)
            ->where('enable', 1)
            ->where('level', $vip->level)
            ->whereIn('type', $types)
            ->where('is_active_for_vip', 1)
            ->get();
    
        foreach ($wares as $ware) {
            self::assignWareToUser($ware, $user, $userVip, $expireTimestamp);
        }
    
        UserVip::where('user_id', $user->id)
            ->where('id', '!=', $userVip->id)
            ->where(function ($q) {
                $q->where("is_used", 1)
                  ->where(fn($q) => $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp));
            })->update(['is_used' => 0]);
    
        $activeVip = UserVip::where('user_id', $user->id)
            ->where('is_used', 1)
            ->where(fn($q) => $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp))
            ->orderByDesc('level')
            ->first();
    
        if ($activeVip) {
            $user->update(['vip' => $activeVip->id]);
        }
    }

    private static function assignWareToUser($ware, $user, $userVip, $expireTimestamp)
    {
        Pack::query()
            ->where('user_id', $user->id)
            ->where('expire', '<', now()->timestamp)
            ->where('expire', '!=', 0)
            ->delete();

        $existingPack = Pack::query()
            ->where('user_id', $user->id)
            ->where('get_type', 1)
            ->where('target_id', $ware->id)
            ->where('vip_user_id', $userVip->id)
            ->where(function ($q) {
                $q->where('expire', '>=', now()->timestamp)
                ->orWhere('expire', 0);
            })->first();

        Pack::query()
            ->where('user_id', $user->id)
            ->where('type', $ware->type)
            ->where('get_type', 1)
            ->where('id', '!=', optional($existingPack)->id)
            ->update(['is_used' => 0]);

        if ($existingPack) {
            $existingPack->update(['is_used' => $userVip->is_used]);
        } else {
            Pack::create([
                'user_id'     => $user->id,
                'get_type'    => $ware->get_type,
                'type'        => $ware->type,
                'target_id'   => $ware->id,
                'num'         => 1,
                'expire'      => $expireTimestamp,
                'use_num'     => $ware->num,
                'vip_user_id' => $userVip->id,
                'is_used'     => $userVip->is_used,
                'using'       => 1,
            ]);
        }

        if (in_array($ware->type, [4, 5, 6])) {
            self::userDress($ware, $user, $userVip->is_used);
            self::unUsePack([$ware->type], $user);
        }
    }

    
    
        private static function getWareTypeName(int $type): string
    {
        $types = [
            1 => 'Gemstone',
            3 => 'Card Scroll',
            4 => 'Avatar Frame',
            5 => 'Bubble Frame',
            6 => 'Entering Special Effects',
            7 => 'Microphone Aperture',
            8 => 'Badge',
            9 => 'NoKick',
            10 => 'Icon',
            11 => 'Intro Animation',
            12 => 'Maple',
            13 => 'Hide Country',
            14 => 'VIP Gifts',
            15 => 'No Pan',
            19 => 'Profile Visitors Hide In',
            20 => 'Hide Last Active',
            28 => 'Profile Frame',
            29 => 'Being Kicked',
            30 => 'Anti Ban',
        ];

        return $types[$type] ?? 'Unknown Type';
    }

    public static function  unUsePack($type, $user)
    {
        Pack::where('type', $type)
            ->where('user_id', $user->id)
            ->where('get_type', '!=', 1)
            ->update(['is_used' => 0]);
    }

    public static function userDress($ware, $user, $isUsed)
    {
        $dressFieldMap = [
            4 => 'dress_1',
            5 => 'dress_2',
            6 => 'dress_3',
        ];

        if (isset($dressFieldMap[$ware->type])) {
            $field = $dressFieldMap[$ware->type];
            $user->$field = $isUsed ? $ware->id : null;
            $user->save();
        }
    }



}