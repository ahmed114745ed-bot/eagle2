<?php

namespace App\Traits\User;

use App\Helpers\UserCommon;
use App\Models\Coin;
use App\Models\CoinLog;
use App\Models\User;


trait PaymentTrait
{

    /**
     * @param $orderId
     * @param mixed $productId
     * @param int|string|null $userId
     * @param $type = null
     * @return false|CoinLog
     */
    public function makePayment($orderId, mixed $productId, int|string|null $userId, $type = null): false | CoinLog
    {
        if ($userId === null) return false;
        $item  = CoinLog::where("trx", $orderId)->first();
        $coins = Coin::find(2);

        $data = false;

        if (!$item && $coins) {
            $user = User::find($userId);

            $user->di += $coins->coin;
            $user->save();
            UserCommon::addChargeLevel($user->id,$coins->coin);
            $data = CoinLog::create([
                "obtained_coins" => $coins?->coin,
                "user_id"        => $userId,
                'method'         => $type,
                'donor_id'       => 0,
                'donor_type'     => 0,
                'status'         => 1,
                'trx'            => $orderId,
            ]);
        }

        UserCommon::updateUserTotalCoins($userId, $coins->coin);

        return $data;
    }

}
