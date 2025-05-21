<?php

namespace Modules\Wallet\Services;

use App\Models\UserWallet;
use Exception;

class CheckAvailableBalance
{
    /**
     * @throws Exception
     */
    public static function checkAvailableBalance(UserWallet $userWalletModel, $amount)
    {
        $userWallet = $userWalletModel::whereUserId(auth()->id())->first();

        if (!$userWallet){
            throw new Exception(__('balance not enough'));
        }

        $totalBalance = $userWallet->value - $userWallet->cut_amount;

        if ($totalBalance < $amount){
            throw new Exception(__('balance not enough'));
        }

        return $userWallet;
    }
}
