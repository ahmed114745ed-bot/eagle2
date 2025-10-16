<?php

namespace App\Traits\User;

use App\Enums\UserCoinLogType;
use App\Helpers\Common;
use App\Helpers\LogHelper;
use App\Helpers\UserCoinLogHelper;
use App\Models\Coin;
use App\Models\ShippingAgency;
use App\Models\User;
use App\Models\CoinLog;
use App\Helpers\UserCommon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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

    public function webhookPayment($orderId, $method = null)
    {
        $coinLog = $this->findCoinLog($orderId, $method);
    
        if (!$coinLog) {
            return $this->transactionNotFoundResponse();
        }
    
        if ($this->isAlreadyProcessed($coinLog)) {
            return $this->alreadyProcessedResponse();
        }
    
        $this->updateCoinLogAsPaid($coinLog);
    
        $this->resolveCoinLogOwner($coinLog);
    
        return $this->finalizeResponse($coinLog);
    }
    
    private function findCoinLog($orderId, $method = null): ?CoinLog
    {
        return CoinLog::where('id', $orderId)
            ->when($method != null, fn($q) => $q->where('method', $method))
            ->first();
    }
    
    private function isAlreadyProcessed(CoinLog $coinLog): bool
    {
        return $coinLog->status == 1;
    }
    
    private function updateCoinLogAsPaid(CoinLog $coinLog): void
    {
        $coinLog->update(['status' => 1]);
    
        LogHelper::info('CoinLog processed', [
            'coinLogId' => $coinLog->id,
            'trx'       => $coinLog->trx,
            'user_id'   => $coinLog->user_id,
        ]);
    }
    
    private function resolveCoinLogOwner(CoinLog $coinLog)
    {
        $owner = $coinLog->owner;

        if ($owner instanceof User) {
            return $this->processUserPayment($owner, $coinLog);
        }

        if ($owner instanceof ShippingAgency) {
            return $this->processAgencyPayment($owner, $coinLog);
        }

    }
    
    private function processUserPayment(User $user, CoinLog $coinLog): void
    {
        $amountBefore = $user->di;
        $user->increment('di', $coinLog->obtained_coins);
    
        UserCoinLogHelper::logByType(
            $user->id,
            $coinLog->obtained_coins,
            $amountBefore,
            UserCoinLogType::PAYMENT
        );
    
        UserCommon::addChargeLevel($user->id, $coinLog->obtained_coins);
    
        (new UserAchievementService())->insertCharging($user, $coinLog->obtained_coins);
    
    }
    
    private function processAgencyPayment(ShippingAgency $agency, CoinLog $coinLog): void
    {
        $agency->increment('coins', $coinLog->obtained_coins);
    }
    
    private function transactionNotFoundResponse()
    {
        return response()->json([
            'status' => 'failed',
            'reason' => 'Transaction not found',
        ]);
    }
    
    private function alreadyProcessedResponse()
    {
        return response()->json([
            'status' => 'failed',
            'reason' => 'Transaction already processed',
        ]);
    }
    
    private function finalizeResponse(CoinLog $coinLog)
    {
        return response()->json([
            'status'  => true,
            'trx'     => $coinLog->trx,
            'message' => 'Transaction completed successfully.',
        ]);
    }
    

}
