<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckWallet;
use Illuminate\Support\Facades\Redis;

/**
 * PoolManager V7: Atomic lucky wallet operations.
 *
 * creditBet: adds net_bet to vault (Redis + DB).
 * debitPayout: atomically deducts payout, supports negative vault limit.
 *
 * App wallet is NOT touched here — handled by FairLuckServiceV7.
 */
class PoolManager
{
    public function getBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
    }

    public function creditBet(int $netBet): void
    {
        if ($netBet <= 0) return;

        FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $netBet);
        FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $netBet, 'V7 bet credit', null);
    }

    /**
     * Atomically debit payout from vault.
     * Supports negative vault up to configurable limit.
     * Uses a single Lua script for atomic check-and-debit.
     */
    public function debitPayout(int $amount): bool
    {
        if ($amount <= 0) return true;

        $negativeLimit = (int) \App\Models\FairLuckSetting::getByKey('V7_negative_limit', 30_000);
        $key = 'fairluck:wallet:' . FairLuckWallet::TYPE_GLOBAL_VAULT;

        // Atomic Lua script: check if (balance - amount) >= -negativeLimit, then debit
        // Redis EVAL is safe — executes Lua on Redis server, not PHP eval()
        $luaScript = "local bal = tonumber(redis.call('GET', KEYS[1]) or 0) "
            . "local amt = tonumber(ARGV[1]) "
            . "local lim = tonumber(ARGV[2]) "
            . "if (bal - amt) >= -lim then redis.call('DECRBY', KEYS[1], amt) return 1 end "
            . "return 0";

        $result = Redis::command('eval', [$luaScript, 1, $key, $amount, $negativeLimit]);

        if (!$result) {
            return false;
        }

        // Redis confirmed — persist to DB
        try {
            FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount, 'V7 win payout', null);
        } catch (\Throwable $e) {
            // DB failed — re-credit Redis to stay in sync
            Redis::incrby($key, $amount);
            return false;
        }

        return true;
    }

    public function getWalletBalances(): array
    {
        return ['lucky_wallet' => $this->getBalance()];
    }
}
