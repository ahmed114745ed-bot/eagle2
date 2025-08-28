<?php

namespace App\Traits\User;

use App\Enums\UserCoinLogType;
use App\Helpers\Common;
use App\Helpers\UserCoinLogHelper;
use App\Models\Coin;
use App\Models\User;
use App\Models\CoinLog;
use App\Helpers\UserCommon;
use Illuminate\Http\JsonResponse;
use Modules\Achievement\Http\Services\UserAchievementService;


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

            $amountBefore = $user->di;
            UserCoinLogHelper::logByType(
                $user->id,
                 $coins->coin,
                $amountBefore,
                UserCoinLogType::PAYMENT,
            );

            $user->di += $coins->coin;
            $user->save();
            UserCommon::addChargeLevel($user->id, $coins->coin);
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

    public function webhookPayment($coinLogId)
    {
        $coinLog = CoinLog::where("id", $coinLogId)->first();

        if (!$coinLog || $coinLog->status == 1) {
            return response()->json(['status' => 'failed', 'reason' => 'Item not found or already processed']);
        }

        $coinLog->status = 1;
        $coinLog->save();

        \Log::error("coinLogId Signature coinLogId ", [
            'coinLog' => $coinLogId,
            '$coinLog->user' => $coinLog->user,

        ]);
        $user = $coinLog->user;
        $amountBefore = $user->di;
        if ($user) {


            UserCoinLogHelper::logByType(
                $user->id,
                $coinLog->obtained_coins,
                $amountBefore,
                UserCoinLogType::PAYMENT,
            );

            $user->di += $coinLog->obtained_coins;
            $user->save();

            info("obtained_coins Signature coinLogId ", [
                'coinLog' => $coinLogId,
                '$coinLog->obtained_coins' => $coinLog->obtained_coins,
                'user_id'   => $user->id,
                'user_di'   => $user->di,
            ]);
            UserCommon::addChargeLevel($user->id, $coinLog->obtained_coins);
            if ($user instanceof User) {
                (new UserAchievementService())->insertCharging($user, $coinLog->obtained_coins);
            }
        } else {
            return response()->json([
                'status'  => false,
                'trx'     => $coinLog->trx,
                'message' => 'Transaction failed.',
            ]);
        }
        response()->json([
            'status'  => true,
            'trx'     => $coinLog?->trx,
            'message' => 'Transaction completed successfully.',
        ]);
    }
}
