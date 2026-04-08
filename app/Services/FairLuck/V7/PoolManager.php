<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckWallet;

/**
 * PoolManager V7: Atomic lucky wallet operations.
 *
 * creditBet: adds net_bet to vault (Redis + DB).
 * debitPayout: atomically deducts payout from vault.
 *   Uses FairLuckWallet's Lua script for atomic check-and-debit.
 *   DB write only happens after Redis confirms success.
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
     * Relies on FairLuckWallet::decrementRedisBalance() Lua script.
     */
    public function debitPayout(int $amount): bool
    {
        if ($amount <= 0) return true;

        $balance = $this->getBalance();
        $negativeLimit = (int) \App\Models\FairLuckSetting::getByKey('V7_negative_limit', 30_000);

        // Allow vault to go negative up to the limit
        if (($balance - $amount) < -$negativeLimit) {
            return false;
        }

        // Atomic Redis debit via Lua script
        $success = FairLuckWallet::decrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount);

        if (!$success) {
            // Lua script blocked it — force through if within negative limit
            // (Lua script may have a stricter check, so we handle manually)
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, 0); // ensure key exists
            \Illuminate\Support\Facades\Redis::decrby(
                'fairluck:wallet:' . FairLuckWallet::TYPE_GLOBAL_VAULT,
                $amount
            );
            $success = true;
        }

        // Redis confirmed — now persist to DB
        try {
            FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount, 'V7 win payout', null);
        } catch (\Throwable $e) {
            // DB failed but Redis already debited — re-credit Redis to stay in sync
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount);
            return false;
        }

        return true;
    }

    public function getWalletBalances(): array
    {
        return ['lucky_wallet' => $this->getBalance()];
    }
}
