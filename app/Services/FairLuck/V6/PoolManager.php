<?php

namespace App\Services\FairLuck\V6;

use App\Models\FairLuckWallet;

class PoolManager
{
    /**
     * Get total unified pool balance (sum of all 3 wallets).
     * Treats all wallets as ONE pool for decision-making.
     */
    public function getTotalBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT)
            + FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET)
            + FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET);
    }

    public function getWalletBalances(): array
    {
        return [
            'global_vault' => FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT),
            'medium_wallet' => FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET),
            'jackpot_wallet' => FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET),
        ];
    }

    /**
     * Distribute bet contribution to wallets (65/20/15 split).
     */
    public function distributeBet(float $amount): void
    {
        if ($amount <= 0) return;

        $global = (int) round($amount * 0.65);
        $jackpot = (int) round($amount * 0.20);
        $medium = (int) round($amount * 0.15);

        if ($global > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $global);
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $global, 'V6 Bet contribution (65%)', null);
        }
        if ($jackpot > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $jackpot);
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $jackpot, 'V6 Bet contribution (20%)', null);
        }
        if ($medium > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $medium);
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $medium, 'V6 Bet contribution (15%)', null);
        }
    }

    /**
     * Pay out a win using cascading fallback across wallets.
     * NEVER fails if total pool has enough - cascades between wallets.
     */
    public function payout(int $amount, int $multiplier, int $userId): bool
    {
        if ($amount <= 0) return true;

        $totalBalance = $this->getTotalBalance();
        $negativeLimit = \App\Models\FairLuckSetting::getNegativeLimit();
        if (($totalBalance + $negativeLimit) < $amount) {
            return false;
        }

        $description = "V6 Win payout ({$multiplier}x)";

        // Determine wallet priority based on multiplier tier
        if ($multiplier >= 250) {
            $walletOrder = [
                FairLuckWallet::TYPE_JACKPOT_WALLET,
                FairLuckWallet::TYPE_GLOBAL_VAULT,
                FairLuckWallet::TYPE_MEDIUM_WALLET,
            ];
        } elseif ($multiplier >= 50) {
            $walletOrder = [
                FairLuckWallet::TYPE_MEDIUM_WALLET,
                FairLuckWallet::TYPE_GLOBAL_VAULT,
                FairLuckWallet::TYPE_JACKPOT_WALLET,
            ];
        } else {
            $walletOrder = [
                FairLuckWallet::TYPE_GLOBAL_VAULT,
                FairLuckWallet::TYPE_MEDIUM_WALLET,
                FairLuckWallet::TYPE_JACKPOT_WALLET,
            ];
        }

        $this->cascadePayout($amount, $walletOrder, $description, $userId);

        return true;
    }

    /**
     * Cascade payout across wallets - deduct from each in priority order.
     */
    private function cascadePayout(int $remaining, array $walletOrder, string $description, int $userId): void
    {
        foreach ($walletOrder as $walletType) {
            if ($remaining <= 0) break;

            $balance = FairLuckWallet::getRedisBalance($walletType);
            $deduct = min($remaining, max(0, $balance));

            if ($deduct > 0) {
                FairLuckWallet::decrementRedisBalance($walletType, $deduct);
                FairLuckWallet::decreaseBalance($walletType, $deduct, $description, $userId);
                $remaining -= $deduct;
            }
        }

        // Last resort: allow global vault to go negative up to limit
        if ($remaining > 0) {
            $negativeLimit = FairLuckWallet::getNegativeLimit();
            $globalBalance = FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);

            if ($globalBalance + $negativeLimit >= $remaining) {
                FairLuckWallet::decrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $remaining);
                FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $remaining, $description . ' (overflow)', $userId);
            }
        }
    }
}
