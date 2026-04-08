<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckSetting;

/**
 * MultiplierTable V7: Single-step weighted selection engine.
 *
 * All tiers (including 0x no-win) compete in one weighted random draw.
 * Weight adjustments are driven by wallet health and per-user RTP.
 *
 * Admin-configurable via FairLuckSetting (with sensible defaults).
 * Octane-safe: No static mutable state. Uses random_int().
 */
class MultiplierTable
{
    /** Default app fee rate (admin-configurable) */
    public const DEFAULT_APP_FEE_RATE = 0.015;

    /** Win tier multipliers */
    private const WIN_TIERS = [5, 10, 20, 50, 100, 250, 500, 1000];

    /** Cached config — loaded once, reused per instance. Safe for Octane (bind, not singleton). */
    private ?array $cachedThresholds = null;
    private ?array $cachedSensitivity = null;
    private ?array $cachedBaseWeights = null;
    private ?float $cachedTargetRTP = null;
    private ?float $cachedAppFeeRate = null;

    /**
     * Preload all settings into memory. Call once before batch processing.
     * In production (bind per request), settings load once per request.
     * In simulation, call explicitly before the loop.
     */
    public function preload(): self
    {
        $this->cachedThresholds = $this->loadThresholds();
        $this->cachedSensitivity = $this->loadSensitivity();
        $this->cachedBaseWeights = $this->loadBaseWeights();
        $this->cachedTargetRTP = (float) FairLuckSetting::getByKey('V7_target_rtp', 0.99);
        $this->cachedAppFeeRate = (float) FairLuckSetting::getByKey('V7_app_fee_rate', self::DEFAULT_APP_FEE_RATE);
        return $this;
    }

    /**
     * Base weights scaled to integers (sum = 100,000).
     * These are the DEFAULT weights — admin can override via FairLuckSetting.
     */
    private const DEFAULT_BASE_WEIGHTS = [
        0    => 93_445,
        5    => 4_000,
        10   => 1_500,
        20   => 600,
        50   => 220,
        100  => 110,
        250  => 65,
        500  => 38,
        1000 => 22,
    ];

    /**
     * Get the app fee rate (admin-configurable, cached per instance).
     */
    public static function getAppFeeRate(): float
    {
        return (float) FairLuckSetting::getByKey('V7_app_fee_rate', self::DEFAULT_APP_FEE_RATE);
    }

    /** Instance-level fee rate (uses cache if preloaded). */
    public function appFeeRate(): float
    {
        return $this->cachedAppFeeRate ?? self::getAppFeeRate();
    }

    private function getThresholds(): array
    {
        return $this->cachedThresholds ?? $this->loadThresholds();
    }

    private function loadThresholds(): array
    {
        return [
            'min'    => (int) FairLuckSetting::getByKey('V7_wallet_min', 10_000),
            'tight'  => (int) FairLuckSetting::getByKey('V7_wallet_tight', 50_000),
            'target' => (int) FairLuckSetting::getByKey('V7_wallet_target', 200_000),
            'high'   => (int) FairLuckSetting::getByKey('V7_wallet_high', 500_000),
            'drain'  => (int) FairLuckSetting::getByKey('V7_wallet_drain', 1_000_000),
        ];
    }

    private function getTargetRTP(): float
    {
        return $this->cachedTargetRTP ?? (float) FairLuckSetting::getByKey('V7_target_rtp', 0.99);
    }

    private function getBaseWeights(): array
    {
        return $this->cachedBaseWeights ?? $this->loadBaseWeights();
    }

    private function loadBaseWeights(): array
    {
        $json = FairLuckSetting::getByKey('V7_base_weights', null);
        if ($json) {
            $decoded = is_string($json) ? json_decode($json, true) : $json;
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }
        return self::DEFAULT_BASE_WEIGHTS;
    }

    private function getAdjustmentSensitivity(): array
    {
        return $this->cachedSensitivity ?? $this->loadSensitivity();
    }

    private function loadSensitivity(): array
    {
        return [
            'no_win_factor'         => (float) FairLuckSetting::getByKey('V7_nowin_sensitivity', 0.08),
            'win_base_factor'       => (float) FairLuckSetting::getByKey('V7_win_base_sensitivity', 0.5),
            'win_position_factor'   => (float) FairLuckSetting::getByKey('V7_win_position_sensitivity', 1.5),
            'boost_base_factor'     => (float) FairLuckSetting::getByKey('V7_boost_base_sensitivity', 0.3),
            'boost_position_factor' => (float) FairLuckSetting::getByKey('V7_boost_position_sensitivity', 1.2),
            'no_win_floor'          => (int) FairLuckSetting::getByKey('V7_nowin_floor', 50_000),
            'wallet_weight'         => (float) FairLuckSetting::getByKey('V7_wallet_weight', 0.60),
            'rtp_weight'            => (float) FairLuckSetting::getByKey('V7_rtp_weight', 0.40),
            'rtp_activation'        => (int) FairLuckSetting::getByKey('V7_rtp_activation', 500),
            'max_loss_streak'       => (int) FairLuckSetting::getByKey('V7_max_loss_streak', 20),
            'forced_win_mult'       => (int) FairLuckSetting::getByKey('V7_forced_win_mult', 5),
        ];
    }

    /**
     * Select a multiplier via single-step weighted random selection.
     *
     * @return array{multiplier: int, walletFactor: float, rtpFactor: float, walletZone: string, jackpotGateFired: bool}
     */
    /**
     * @param int $currentLossStreak Current consecutive losses (0 = last spin was a win)
     */
    public function select(
        int $luckyBalance,
        int $userTotalBet,
        int $userTotalReturned,
        int $betAmount,
        int $currentLossStreak = 0
    ): array {
        $sens = $this->getAdjustmentSensitivity();
        $thresholds = $this->getThresholds();

        // 1. Wallet health factor
        $wFactor = $this->walletFactor($luckyBalance, $thresholds);

        // 2. Per-user RTP factor
        $currentRTP = $userTotalBet > 0 ? $userTotalReturned / $userTotalBet : 0.0;
        $rFactor = $this->rtpFactor($userTotalBet, $currentRTP, $sens);

        // 3. Combined factor
        $combinedFactor = ($wFactor * $sens['wallet_weight']) + ($rFactor * $sens['rtp_weight']);

        // 4. Adjust weights
        $baseWeights = $this->getBaseWeights();
        $weights = $this->adjustWeights($combinedFactor, $baseWeights, $sens);

        // 5. Hard jackpot gate
        $appFeeRate = self::getAppFeeRate();
        $netBet = $betAmount - (int) round($betAmount * $appFeeRate);
        $gateFired = $this->applyJackpotGate($weights, $luckyBalance, $netBet, $thresholds['min']);

        // 6. Loss streak protection — force a small win after too many losses
        $maxLossStreak = (int) ($this->cachedSensitivity['max_loss_streak'] ??
            FairLuckSetting::getByKey('V7_max_loss_streak', 20));
        $forcedMult = (int) ($this->cachedSensitivity['forced_win_mult'] ??
            FairLuckSetting::getByKey('V7_forced_win_mult', 5));

        if ($maxLossStreak > 0 && $currentLossStreak >= $maxLossStreak) {
            return [
                'multiplier'       => $forcedMult,
                'walletFactor'     => round($wFactor, 4),
                'rtpFactor'        => round($rFactor, 4),
                'walletZone'       => $this->getZone($luckyBalance, $thresholds),
                'jackpotGateFired' => $gateFired,
                'forcedWin'        => true,
            ];
        }

        // 7. Weighted selection
        $multiplier = $this->weightedSelect($weights);

        return [
            'multiplier'       => $multiplier,
            'walletFactor'     => round($wFactor, 4),
            'rtpFactor'        => round($rFactor, 4),
            'walletZone'       => $this->getZone($luckyBalance, $thresholds),
            'jackpotGateFired' => $gateFired,
            'forcedWin'        => false,
        ];
    }

    /**
     * Piecewise linear wallet health factor: [-1.0, +1.0].
     */
    public function walletFactor(int $balance, ?array $t = null): float
    {
        $t = $t ?? $this->getThresholds();

        // Negative vault: maximum suppression (factor = -1.0)
        if ($balance <= 0) return -1.0;
        if ($balance <= $t['min']) return -1.0;
        if ($balance <= $t['tight']) {
            return -1.0 + 0.4 * (($balance - $t['min']) / max(1, $t['tight'] - $t['min']));
        }
        if ($balance <= $t['target']) {
            return -0.6 + 0.6 * (($balance - $t['tight']) / max(1, $t['target'] - $t['tight']));
        }
        if ($balance <= $t['high']) {
            return 0.0 + 0.5 * (($balance - $t['target']) / max(1, $t['high'] - $t['target']));
        }
        if ($balance <= $t['drain']) {
            return 0.5 + 0.5 * (($balance - $t['high']) / max(1, $t['drain'] - $t['high']));
        }
        return 1.0;
    }

    /**
     * Per-user RTP correction factor: [-1.0, +1.0].
     */
    public function rtpFactor(int $totalBet, float $currentRTP, ?array $sens = null): float
    {
        $sens = $sens ?? $this->getAdjustmentSensitivity();

        if ($totalBet < $sens['rtp_activation']) {
            return 0.0;
        }

        $target = $this->getTargetRTP();
        $gap = $target - $currentRTP;
        $clamped = max(-0.15, min(0.15, $gap));

        return $clamped / 0.15;
    }

    /**
     * Adjust base weights using the combined factor.
     */
    public function adjustWeights(float $combinedFactor, ?array $baseWeights = null, ?array $sens = null): array
    {
        $baseWeights = $baseWeights ?? $this->getBaseWeights();
        $sens = $sens ?? $this->getAdjustmentSensitivity();
        $adjusted = [];

        // 0x tier
        $zeroBase = $baseWeights[0] ?? 93_445;
        $zeroWeight = $zeroBase * (1 - $combinedFactor * $sens['no_win_factor']);
        $adjusted[0] = max($sens['no_win_floor'], (int) round($zeroWeight));

        // Win tiers
        $tierCount = count(self::WIN_TIERS);
        foreach (self::WIN_TIERS as $i => $mult) {
            $tierPosition = $i / max(1, $tierCount - 1);
            $base = $baseWeights[$mult] ?? 0;

            if ($combinedFactor < 0) {
                $factor = 1.0 + $combinedFactor * ($sens['win_base_factor'] + $tierPosition * $sens['win_position_factor']);
            } else {
                $factor = 1.0 + $combinedFactor * ($sens['boost_base_factor'] + $tierPosition * $sens['boost_position_factor']);
            }

            $adjusted[$mult] = max(1, (int) round($base * $factor));
        }

        return $adjusted;
    }

    /**
     * Hard jackpot gate: block tiers that would breach wallet floor.
     */
    public function applyJackpotGate(array &$weights, int $luckyBalance, int $netBet, int $walletMin = 10_000): bool
    {
        $gateFired = false;

        foreach (self::WIN_TIERS as $mult) {
            if (!isset($weights[$mult]) || $weights[$mult] <= 0) continue;

            $worstCasePayout = $netBet * $mult;
            if (($luckyBalance - $worstCasePayout) < $walletMin) {
                $weights[$mult] = 1;
                $gateFired = true;
            }
        }

        return $gateFired;
    }

    /**
     * Weighted random selection using random_int().
     */
    private function weightedSelect(array $weights): int
    {
        $total = array_sum($weights);
        if ($total <= 0) return 0;

        $roll = random_int(1, $total);
        $cumulative = 0;

        foreach ($weights as $mult => $w) {
            $cumulative += $w;
            if ($roll <= $cumulative) return $mult;
        }

        return 0;
    }

    /**
     * Get wallet zone name.
     */
    public function getZone(int $balance, ?array $t = null): string
    {
        $t = $t ?? $this->getThresholds();

        if ($balance <= $t['min']) return 'CRITICAL';
        if ($balance <= $t['tight']) return 'TIGHT';
        if ($balance <= $t['target']) return 'NORMAL';
        if ($balance <= $t['high']) return 'GENEROUS';
        return 'DRAIN';
    }

    /**
     * Get wallet minimum constant.
     */
    public static function getWalletMin(): int
    {
        return (int) FairLuckSetting::getByKey('V7_wallet_min', 10_000);
    }
}
