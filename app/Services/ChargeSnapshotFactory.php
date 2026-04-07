<?php

namespace App\Services;

use App\Models\User;

/**
 * ChargeSnapshotFactory - Centralized factory for creating charge snapshot data.
 * 
 * This service ensures consistent snapshot creation across all entry points:
 * - ChargeController
 * - SalaryTransaction
 * - SuperAdminChargeAction
 * - AreaManagerChargeAction
 * - BdWalletController
 * - UsersChargeController
 * 
 * Usage:
 *   $snapshot = ChargeSnapshotFactory::create(100, 'usd', $adminUser);
 *   $charge->fill($snapshot);
 */
class ChargeSnapshotFactory
{
    /**
     * Create a complete charge snapshot with all calculated fields.
     *
     * @param float $amount The amount to charge (in USD or coins)
     * @param string $unit 'usd' or 'coins'
     * @param mixed|null $admin The admin/user performing the charge (for rate resolution)
     * @param string|null $rateSource Optional override for rate_source field
     * @return array Complete snapshot data ready to be saved to Charge model
     * @throws \Exception
     */
    public static function create(float $amount, string $unit = 'usd', $admin = null, ?string $rateSource = null): array
    {
        $appBaseRate = CoinRateService::getAppBaseRate();
        $effectiveRate = CoinRateService::getEffectiveRate($admin);
        
        $calc = ChargeCalculationService::calculate($amount, $unit, $effectiveRate, $appBaseRate);
        
        // Determine rate source if not provided
        if ($rateSource === null) {
            $rateSource = self::determineRateSource($admin, $effectiveRate, $appBaseRate);
        }
        
        return [
            'applied_coin_rate' => $calc['applied_coin_rate'],
            'base_usd'          => $calc['base_usd'],
            'base_coins'        => $calc['base_coins'],
            'bonus_coins'       => $calc['bonus_coins'],
            'total_coins'       => $calc['total_coins'],
            'profit_usd'        => $calc['profit_usd'],
            'profit_coins'      => $calc['profit_coins'],
            'rate_source'       => $rateSource,
        ];
    }

    /**
     * Create snapshot for user-to-user transfers (P2P).
     * Uses the user transfer rate instead of admin rates.
     *
     * @param float $amount The amount to transfer
     * @param string $unit 'usd' or 'coins'
     * @return array Complete snapshot data
     * @throws \Exception
     */
    public static function createForUserTransfer(float $amount, string $unit = 'usd'): array
    {
        $appBaseRate = CoinRateService::getAppBaseRate();
        $transferRate = CoinRateService::getUserTransferRate();
        
        $calc = ChargeCalculationService::calculate($amount, $unit, $transferRate, $appBaseRate);
        
        return [
            'applied_coin_rate' => $calc['applied_coin_rate'],
            'base_usd'          => $calc['base_usd'],
            'base_coins'        => $calc['base_coins'],
            'bonus_coins'       => $calc['bonus_coins'],
            'total_coins'       => $calc['total_coins'],
            'profit_usd'        => $calc['profit_usd'],
            'profit_coins'      => $calc['profit_coins'],
            'rate_source'       => 'user_transfer',
        ];
    }

    /**
     * Create snapshot with explicit cashback/bonus coins.
     *
     * @param float $amount The base amount
     * @param string $unit 'usd' or 'coins'
     * @param float $extraCoins Additional bonus coins to add
     * @param mixed|null $admin The admin performing the charge
     * @return array Complete snapshot data with bonus
     * @throws \Exception
     */
    public static function createWithCashback(float $amount, string $unit, float $extraCoins, $admin = null): array
    {
        $snapshot = self::create($amount, $unit, $admin);
        
        // Add extra cashback coins
        $snapshot['bonus_coins'] = $snapshot['bonus_coins'] + $extraCoins;
        $snapshot['total_coins'] = $snapshot['base_coins'] + $snapshot['bonus_coins'];
        
        return $snapshot;
    }

    /**
     * Determine the rate source based on the admin and rates used.
     *
     * @param mixed|null $admin
     * @param float $effectiveRate
     * @param float $appBaseRate
     * @return string
     */
    protected static function determineRateSource($admin, float $effectiveRate, float $appBaseRate): string
    {
        if (!$admin) {
            return 'app';
        }

        // Check if using custom rate
        if ($admin->id ?? null) {
            $customRate = \App\Models\AdminCoinRate::where('admin_id', $admin->id)->value('rate');
            if ($customRate && (float) $customRate === $effectiveRate) {
                return 'custom';
            }
        }

        // Check if using role rate
        if ($effectiveRate !== $appBaseRate) {
            return 'role';
        }

        return 'app';
    }

    /**
     * Validate that all required snapshot fields are present.
     *
     * @param array $data
     * @return bool
     */
    public static function validateSnapshot(array $data): bool
    {
        $requiredFields = [
            'applied_coin_rate',
            'base_usd',
            'base_coins',
            'bonus_coins',
            'total_coins',
            'profit_usd',
            'profit_coins',
        ];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get snapshot fields that should be included in charge creation.
     *
     * @return array
     */
    public static function getSnapshotFields(): array
    {
        return [
            'applied_coin_rate',
            'base_usd',
            'base_coins',
            'bonus_coins',
            'total_coins',
            'profit_usd',
            'profit_coins',
            'rate_source',
        ];
    }
}
