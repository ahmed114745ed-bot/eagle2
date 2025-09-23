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
    
        $existingAccounts = UserAccount::where('device_token', $deviceToken)->get();
    
        if ($existingAccounts->isEmpty()) {
            $otherUsers = \App\Models\User::where('device_token', $deviceToken)
                ->where('id', '!=', $userId)
                ->get();
    
            if ($otherUsers->isNotEmpty()) {
                foreach ($otherUsers as $otherUser) {
                    UserAccount::create([
                        'parent_user_id' => $userId,
                        'child_user_id'  => $otherUser->id,
                        'device_token'   => $deviceToken,
                        'key'            => \Illuminate\Support\Str::uuid(),
                        'expire'         => 30,
                    ]);
                }
                return true;
            }
    
            return UserAccount::create([
                'parent_user_id' => $userId,
                'child_user_id'  => null,
                'device_token'   => $deviceToken,
                'key'            => \Illuminate\Support\Str::uuid(),
                'expire'         => 30,
            ]);
        }
    
        foreach ($existingAccounts as $account) {
            $alreadyLinked = UserAccount::where(function ($q) use ($userId, $account) {
                $q->where('parent_user_id', $userId)
                  ->where('child_user_id', $account->parent_user_id);
            })->exists();
    
            if (!$alreadyLinked) {
                UserAccount::create([
                    'parent_user_id' => $userId,
                    'child_user_id'  => $account->parent_user_id,
                    'device_token'   => $deviceToken,
                    'key'            => \Illuminate\Support\Str::uuid(),
                    'expire'         => 30,
                ]);
            }
        }
    
        return true;
    }
}
