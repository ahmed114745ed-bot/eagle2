<?php
namespace App\helper;

use Illuminate\Support\Str;
use Modules\SwitchAccount\Entities\UserAccount;

class AccountHelper
{
    public static function linkAccountWithDevice($parentUserId, $childUserId, $deviceToken)
    {
        UserAccount::where(function ($q) use ($childUserId, $parentUserId) {
            $q->where('child_user_id', $childUserId)
              ->orWhere('parent_user_id', $childUserId);
        })
        ->delete();

        $key = Str::uuid();

        return UserAccount::create([
            'parent_user_id' => $parentUserId,
            'child_user_id'  => $childUserId,
            'device_token'   => $deviceToken,
            'key'            => $key,
            'expire'         => 30,
        ]);
    }


    public static function linkLoginAccountWithDevice(int $userId, string $deviceToken)
    {
        if (empty($deviceToken)) {
            return null;
        }

        UserAccount::where(function ($q) use ($userId) {
            $q->where('parent_user_id', $userId)
              ->orWhere('child_user_id', $userId);
        })->delete();

        $linkedUserIds = UserAccount::where('device_token', $deviceToken)
            ->pluck('parent_user_id')
            ->merge(
                UserAccount::where('device_token', $deviceToken)->pluck('child_user_id')
            )
            ->filter()
            ->unique()
            ->toArray();

        $usersWithSameDevice = \App\Models\User::where('device_token', $deviceToken)
            ->where('id', '!=', $userId)
            ->pluck('id')
            ->toArray();

        $allRelatedUsers = collect($linkedUserIds)
            ->merge($usersWithSameDevice)
            ->unique()
            ->values()
            ->toArray();
     
        foreach ($allRelatedUsers as $otherUserId) {
            UserAccount::create([
                'parent_user_id' => $userId,
                'child_user_id'  => $otherUserId,
                'device_token'   => $deviceToken,
                'key'            => \Illuminate\Support\Str::uuid(),
                'expire'         => 30,
            ]);
        }

        if (empty($allRelatedUsers)) {
     
            return UserAccount::create([
                'parent_user_id' => $userId,
                'child_user_id'  => null,
                'device_token'   => $deviceToken,
                'key'            => \Illuminate\Support\Str::uuid(),
                'expire'         => 30,
            ]);
        }

        return true;
    }
}
