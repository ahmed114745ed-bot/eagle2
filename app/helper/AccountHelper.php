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
}
