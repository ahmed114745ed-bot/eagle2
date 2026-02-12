<?php

namespace App\Services\FairLuck;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class HighMultiplierLedger
{
    private const SCORES_KEY = 'fairluck_high_multiplier_scores';
    private const POOL_KEY = 'fairluck_high_multiplier_pool';
    private const USER_KEY_PREFIX = 'fairluck_high_multiplier_user_';
    private const POOL_ALERT_KEY = 'fairluck_high_multiplier_pool_alerted';

    public function evaluateEligibility(int $userId, float $betAmount): HighMultiplierSignal
    {
        $score = $this->getScore($userId);
        if ($score <= 0) {
            return HighMultiplierSignal::empty();
        }

        $rank = $this->getRank($userId);
        $rankWindow = (int) config('fairluck.high_multiplier.rank_window', 25);
        if ($rank === null || $rank >= $rankWindow) {
            return HighMultiplierSignal::empty($score, $rank !== null ? $rank + 1 : null);
        }

        $tiers = $this->getTierThresholds();
        $pool = $this->getPool();
        $eligibleTier = null;
        $coverRatio = (float) config('fairluck.high_multiplier.pool_cover_ratio', 0.85);

        foreach ($tiers as $multiplier => $threshold) {
            if ($score < $threshold) {
                continue;
            }

            $requiredProfit = max(0, $betAmount * ($multiplier - 1));
            if ($pool >= ($requiredProfit * $coverRatio)) {
                $eligibleTier = (int) $multiplier;
            }
        }

        if ($eligibleTier === null) {
            return HighMultiplierSignal::empty($score, $rank + 1);
        }

        $priorityMultipliers = array_values(array_filter(array_keys($tiers), fn ($tier) => $tier <= $eligibleTier));
        $probabilityFloors = config('fairluck.high_multiplier.probability_floors', []);
        $probabilityFloor = (float) ($probabilityFloors[$eligibleTier] ?? 0.0);
        $weightBoostsConfig = config('fairluck.high_multiplier.weight_boosts', []);
        $weightBoosts = [];
        foreach ($priorityMultipliers as $priorityMultiplier) {
            $weightBoosts[$priorityMultiplier] = (float) ($weightBoostsConfig[$priorityMultiplier] ?? 12);
        }

        $forceJackpot = $eligibleTier >= 250;

        return new HighMultiplierSignal(
            $score,
            $rank + 1,
            $eligibleTier,
            $priorityMultipliers,
            $probabilityFloor,
            $weightBoosts,
            $forceJackpot
        );
    }

    public function recordOutcome(int $userId, float $betAmount, float $profitAmount, bool $isWinner, int $multiplier): void
    {
        $scoreDelta = 0.0;

        if ($isWinner) {
            $scoreDelta += $this->penalizeWin($betAmount, $profitAmount, $multiplier);
            $this->drainPool($profitAmount);
        } else {
            $scoreDelta += $this->rewardLoss($betAmount, $profitAmount);
            $this->feedPool(abs($profitAmount));
        }

        if ($scoreDelta !== 0.0) {
            Redis::zincrby(self::SCORES_KEY, $scoreDelta, $userId);
            $this->normalizeScore($userId);
        }

        $this->rememberUser($userId, $betAmount, $profitAmount, $isWinner, $multiplier);
    }

    public function leaderboard(int $limit = 10): array
    {
        $maxLimit = (int) config('fairluck.high_multiplier.leaderboard_limit', 50);
        $limit = max(1, min($limit, $maxLimit));
        $raw = Redis::zrevrange(self::SCORES_KEY, 0, $limit - 1, ['withscores' => true]);

        $result = [];
        foreach ($raw as $member => $score) {
            $result[] = [
                'user_id' => (int) $member,
                'score' => (float) $score,
            ];
        }

        return $result;
    }

    public function getPool(): float
    {
        return (float) (Redis::get(self::POOL_KEY) ?? 0.0);
    }

    private function getScore(int $userId): float
    {
        $score = Redis::zscore(self::SCORES_KEY, $userId);
        return $score === null ? 0.0 : (float) $score;
    }

    private function getRank(int $userId): ?int
    {
        $rank = Redis::zrevrank(self::SCORES_KEY, $userId);
        if ($rank === false || $rank === null) {
            return null;
        }

        return (int) $rank;
    }

    private function getTierThresholds(): array
    {
        $tiers = config('fairluck.high_multiplier.tiers', []);
        ksort($tiers, SORT_NUMERIC);

        return $tiers;
    }

    private function rewardLoss(float $betAmount, float $profitAmount): float
    {
        if ($profitAmount >= 0) {
            return 0.0;
        }

        $lossMagnitude = abs($profitAmount);
        $lossWeight = (float) config('fairluck.high_multiplier.loss_score_weight', 1.0);
        $betWeight = (float) config('fairluck.high_multiplier.bet_score_weight', 0.1);

        return ($lossMagnitude * $lossWeight) + ($betAmount * $betWeight);
    }

    private function penalizeWin(float $betAmount, float $profitAmount, int $multiplier): float
    {
        if ($profitAmount <= 0) {
            return 0.0;
        }

        if ($multiplier < 100) {
            $ratio = (float) config('fairluck.high_multiplier.small_win_penalty', 0.2);
            $penalty = max($betAmount * $ratio, $profitAmount * 0.1);
            return -$penalty;
        }

        $floorPenalty = (float) config('fairluck.high_multiplier.big_win_floor_penalty', 900);
        $penaltyWeight = (float) config('fairluck.high_multiplier.big_win_penalty_weight', 3.0);

        $tierBonus = match (true) {
            $multiplier >= 500 => 1.6,
            $multiplier >= 250 => 1.25,
            default => 0.85,
        };

        $penalty = max($floorPenalty, $profitAmount * $penaltyWeight) * $tierBonus;

        return -$penalty;
    }

    private function feedPool(float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $rate = (float) config('fairluck.high_multiplier.pool_contribution_rate', 0.4);
        $increment = $amount * $rate;
        if ($increment <= 0) {
            return;
        }

        $next = (float) Redis::incrbyfloat(self::POOL_KEY, $increment);
        $this->checkPoolAlert($next);
    }

    private function drainPool(float $profitAmount): void
    {
        if ($profitAmount <= 0) {
            return;
        }

        $ratio = (float) config('fairluck.high_multiplier.pool_payout_ratio', 1.0);
        $drain = $profitAmount * $ratio;
        if ($drain <= 0) {
            return;
        }

        $current = $this->getPool();
        $next = max(0, $current - $drain);
        Redis::set(self::POOL_KEY, $next);
        $this->checkPoolAlert($next);
    }

    private function normalizeScore(int $userId): void
    {
        $score = Redis::zscore(self::SCORES_KEY, $userId);
        if ($score === null) {
            return;
        }

        if ($score <= 0) {
            Redis::zrem(self::SCORES_KEY, $userId);
        }
    }

    private function rememberUser(int $userId, float $betAmount, float $profitAmount, bool $isWinner, int $multiplier): void
    {
        $key = self::USER_KEY_PREFIX . $userId;
        $timestamp = Carbon::now()->timestamp;
        $data = [
            'updated_at' => $timestamp,
            'last_bet' => $betAmount,
            'last_profit' => $profitAmount,
            'last_multiplier' => $multiplier,
            'last_outcome' => $isWinner ? 'win' : 'loss',
        ];

        Redis::hmset($key, $data);
        Redis::hincrbyfloat($key, 'bet_volume', $betAmount);
        if ($profitAmount < 0) {
            Redis::hincrbyfloat($key, 'loss_volume', abs($profitAmount));
        } elseif ($profitAmount > 0) {
            Redis::hincrbyfloat($key, 'win_volume', $profitAmount);
        }
        if ($isWinner && $multiplier >= 100) {
            Redis::hincrby($key, 'high_multiplier_wins', 1);
            Redis::hset($key, 'last_high_multiplier_at', $timestamp);
        }

        $ttl = (int) config('fairluck.high_multiplier.user_ttl', 604800);
        Redis::expire($key, $ttl);
    }

    private function checkPoolAlert(float $pool): void
    {
        $floor = (float) config('fairluck.high_multiplier.pool_alert_floor', 0);
        if ($floor <= 0) {
            return;
        }

        $alertTtl = max(60, (int) config('fairluck.high_multiplier.pool_alert_ttl', 1800));
        $alerted = (bool) Redis::get(self::POOL_ALERT_KEY);

        if ($pool <= $floor) {
            if (!$alerted) {
                Log::warning('FairLuck high multiplier pool dropped below floor', [
                    'pool' => $pool,
                    'floor' => $floor,
                ]);
                Redis::setex(self::POOL_ALERT_KEY, $alertTtl, 1);
            }
            return;
        }

        if ($alerted) {
            Log::info('FairLuck high multiplier pool recovered above floor', [
                'pool' => $pool,
                'floor' => $floor,
            ]);
        }
        Redis::del(self::POOL_ALERT_KEY);
    }
}
