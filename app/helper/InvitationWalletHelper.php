<?php

namespace App\helper;

use App\Models\InvitationWallet;
use App\Models\User;

class InvitationWalletHelper
{

    public static function addBalance(User $user, float $amount): InvitationWallet
    {
        $wallet = InvitationWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'total_earned' => 0]
        );

        $wallet->increment('balance', $amount);
        $wallet->increment('total_earned', $amount);

        return $wallet->fresh();
    }


    public static function getWallet(User $user): ?InvitationWallet
    {
        return InvitationWallet::where('user_id', $user->id)->first();
    }

 
    public static function deductBalance(User $user, float $amount): bool
    {
        $wallet = self::getWallet($user);

        if (!$wallet || $wallet->balance < $amount) {
            return false;
        }

        $wallet->decrement('balance', $amount);

        return true;
    }
}
