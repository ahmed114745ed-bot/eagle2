<?php

namespace App\Traits\User;

use App\Enums\UserCoinLogType;
use App\Helpers\Common;
use App\Helpers\LogHelper;
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

    public function webhookPayment( $trx , $method = null)
    {
        info('Webhook Payment Triggered', ['trx' => $trx]);

        // Fetch coin log
        $coinLog = CoinLog::where('trx', $trx)
            ->when($method != null, fn($q) => $q->where('method', $method))
            ->first();

        if (!$coinLog) {
            return response()->json([
                'status' => 'failed',
                'reason' => 'Transaction not found',
            ]);
        }

        $coinLogId = $coinLog->id;

        if ($coinLog->status == 1) {
            return response()->json([
                'status' => 'failed',
                'reason' => 'Transaction already processed',
            ]);
        }

        // Mark as processed
        $coinLog->update(['status' => 1, 'trx' => $trx]);

        LogHelper::info('CoinLog processed', [
            'coinLogId' => $coinLogId,
            'trx'       => $coinLog->trx,
            'user_id'   => $coinLog->user_id,
        ]);

        $user = $coinLog->user;

        if (!$user) {
            info('No user found for CoinLog', ['coinLogId' => $coinLogId]);

            return response()->json([
                'status'  => false,
                'trx'     => $coinLog->trx,
                'message' => 'Transaction failed. User not found.',
            ]);
        }

        // Update user balance
        $amountBefore = $user->di;
        $user->increment('di', $coinLog->obtained_coins);

        // Log transaction
        UserCoinLogHelper::logByType(
            $user->id,
            $coinLog->obtained_coins,
            $amountBefore,
            UserCoinLogType::PAYMENT,
        );

        info('Coins credited', [
            'coinLogId'     => $coinLogId,
            'trx'           => $coinLog->trx,
            'obtainedCoins' => $coinLog->obtained_coins,
            'user_id'       => $user->id,
            'balance_after' => $user->di,
        ]);

        // Add extra features
        UserCommon::addChargeLevel($user->id, $coinLog->obtained_coins);

        if ($user instanceof User) {
            (new UserAchievementService())->insertCharging($user, $coinLog->obtained_coins);
        }

        return response()->json([
            'status'  => true,
            'trx'     => $coinLog->trx,
            'message' => 'Transaction completed successfully.',
        ]);
    }

}
