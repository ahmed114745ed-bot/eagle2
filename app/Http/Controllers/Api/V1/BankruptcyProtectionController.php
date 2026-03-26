<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FairLuck\V6\BankruptcyProtection;
use Illuminate\Http\Request;

/**
 * BankruptcyProtectionController: Admin endpoint to monitor system health
 */
class BankruptcyProtectionController extends Controller
{
    public function __construct(
        private BankruptcyProtection $bankruptcyProtection
    ) {}

    /**
     * Get current system health status
     * GET /api/v1/bankruptcy-protection/health
     */
    public function getHealthStatus()
    {
        $health = $this->bankruptcyProtection->getHealthStatus();

        return response()->json([
            'success' => true,
            'data' => $health,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get detailed pool information
     * GET /api/v1/bankruptcy-protection/pool-info
     */
    public function getPoolInfo()
    {
        $totalBalance = $this->bankruptcyProtection->getTotalPoolBalance();
        $minSafe = $this->bankruptcyProtection->getMinimumSafeBalance();
        $critical = $this->bankruptcyProtection->getCriticalThreshold();
        $negativeLimit = $this->bankruptcyProtection->getNegativeLimit();
        $maxPayoutPercentage = $this->bankruptcyProtection->getMaxPayoutPercentage();
        $maxSafePayout = $this->bankruptcyProtection->getMaxSafePayoutAmount();
        $probabilityFactor = $this->bankruptcyProtection->getProbabilityReductionFactor();

        return response()->json([
            'success' => true,
            'data' => [
                'total_balance' => $totalBalance,
                'minimum_safe_balance' => $minSafe,
                'critical_threshold' => $critical,
                'negative_limit' => $negativeLimit,
                'max_payout_percentage' => $maxPayoutPercentage,
                'max_safe_payout_amount' => $maxSafePayout,
                'probability_reduction_factor' => round($probabilityFactor, 4),
                'is_critical' => $this->bankruptcyProtection->isCritical(),
                'can_afford_payout' => $this->bankruptcyProtection->canAffordPayout($maxSafePayout),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Check if system can afford a specific payout
     * POST /api/v1/bankruptcy-protection/can-afford
     */
    public function canAffordPayout(Request $request)
    {
        $validated = $request->validate([
            'payout_amount' => 'required|integer|min:1',
        ]);

        $payoutAmount = $validated['payout_amount'];
        $canAfford = $this->bankruptcyProtection->canAffordPayout($payoutAmount);
        $safePayout = $this->bankruptcyProtection->validateAndCapPayout($payoutAmount);

        return response()->json([
            'success' => true,
            'data' => [
                'requested_payout' => $payoutAmount,
                'can_afford' => $canAfford,
                'safe_payout_amount' => $safePayout,
                'was_capped' => $safePayout < $payoutAmount,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get probability reduction factor
     * GET /api/v1/bankruptcy-protection/probability-factor
     */
    public function getProbabilityFactor()
    {
        $factor = $this->bankruptcyProtection->getProbabilityReductionFactor();
        $health = $this->bankruptcyProtection->getHealthStatus();

        return response()->json([
            'success' => true,
            'data' => [
                'probability_reduction_factor' => round($factor, 4),
                'pool_health_status' => $health['status'],
                'pool_health_percentage' => $health['health_percentage'],
                'explanation' => $this->getProbabilityExplanation($factor, $health['status']),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get human-readable explanation of probability factor
     */
    private function getProbabilityExplanation(float $factor, string $status): string
    {
        return match($status) {
            'healthy' => "Pool is healthy. Win probability is at normal levels (factor: {$factor}).",
            'warning' => "Pool is in warning state. Win probability is reduced to {$factor} (50-100% of normal).",
            'critical' => "Pool is in critical state. Win probability is severely reduced to {$factor} (10-50% of normal).",
            default => "Unknown pool status.",
        };
    }
}
