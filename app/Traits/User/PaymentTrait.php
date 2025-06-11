<?php

namespace App\Traits\User;

use App\Helpers\UserCommon;
use App\Models\Coin;
use App\Models\CoinLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;


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
        $coins = Coin::find($productId);
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

    public function webhookPayment($coinLogId): JsonResponse
    {
        $coinLog = CoinLog::where("id", $coinLogId)->first();

        if (!$coinLog || $coinLog->status == 1) {
            return response()->json(['status' => 'failed', 'reason' => 'Item not found or already processed']);
        }

        $coinLog->status = 1;
        $coinLog->save();

        $user = $coinLog->user;
        if ($user) {
            $user->di += $coinLog->obtained_coins;
            $user->save();
        } else {
            return response()->json(['status' => 'failed', 'reason' => 'User not found']);
        }
        return response()->json(['status' => 'success', 'data' => []]);
    }
}
