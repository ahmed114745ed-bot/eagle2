<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Charge;
use App\Models\Setting;
use App\Models\AdminCoinRate;
use App\Services\ChargeCalculationService;

class BackfillChargeData extends Command
{
    protected $signature = 'charges:backfill
                            {--chunk=500 : Number of records to process per batch}
                            {--dry-run : Print what would be done without making changes}';

    protected $description = 'Backfill old charge records with calculated profit, bonus, and USD data based on legacy rates.';

    private array $adminRateCache = [];

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $chunk    = (int) $this->option('chunk');

        $appBaseRate      = (float) (Setting::where('key', 'app_coin_rate')->value('value') ?? 10000);
        $superAdminRate   = (float) (Setting::where('key', 'super_admin_coins')->value('value') ?? $appBaseRate);
        $areaManagerRate  = (float) (Setting::where('key', 'area_manager_coins')->value('value')
                                     ?? Setting::where('key', 'zones_coins')->value('value')
                                     ?? $appBaseRate);
        $shippingRate     = (float) (Setting::where('key', 'shipping_coins')->value('value') ?? $appBaseRate);
        $userRate         = (float) (Setting::where('key', 'user_transfer_coin_rate')->value('value') ?? $appBaseRate);

        $this->info("Fetched rates → App: {$appBaseRate}  SuperAdmin: {$superAdminRate}  AreaManager: {$areaManagerRate}  Shipping: {$shippingRate}  User: {$userRate}");

        $query = Charge::whereNull('profit_coins');

        $total   = $query->count();
        $updated = 0;
        $this->info("Records to backfill: {$total}");

        if ($isDryRun) {
            $this->warn('[DRY RUN] No changes will be saved.');
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById($chunk, function ($charges) use (
            $appBaseRate, $superAdminRate, $areaManagerRate, $shippingRate, $userRate,
            $isDryRun, &$updated, $bar
        ) {
            foreach ($charges as $charge) {
                $rate = $this->resolveRate(
                    $charge, $appBaseRate, $superAdminRate, $areaManagerRate, $shippingRate, $userRate
                );

                // Determine the input unit
                if (!empty($charge->usd) && $charge->usd > 0) {
                    $unit   = 'usd';
                    $amount = (float) $charge->usd;
                } else {
                    $unit   = 'coins';
                    $amount = (float) $charge->amount;
                }

                $calc = ChargeCalculationService::calculate($amount, $unit, $rate);

                if (!$isDryRun) {
                    $charge->update([
                        'applied_coin_rate' => $calc['applied_coin_rate'],
                        'base_usd'          => $calc['base_usd'],
                        'base_coins'        => $calc['base_coins'],
                        'bonus_coins'       => $calc['bonus_coins'],
                        'profit_usd'        => $calc['profit_usd'],
                        'profit_coins'      => $calc['profit_coins'],
                        'total_coins'       => $calc['total_coins'],
                    ]);
                }

                $updated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done. Records processed: {$updated}" . ($isDryRun ? ' (dry-run, no DB changes)' : '.'));
        return Command::SUCCESS;
    }

  
    private function resolveRate(
        Charge $charge,
        float $appBaseRate,
        float $superAdminRate,
        float $areaManagerRate,
        float $shippingRate,
        float $userRate
    ): float {
        // Prefer already saved rate if present
        if (!empty($charge->applied_coin_rate) && $charge->applied_coin_rate > 0) {
            return (float) $charge->applied_coin_rate;
        }

        $chargerType = $charge->charger_type;

        if ($chargerType === 'dash') {
            return $appBaseRate;
        }

        if ($chargerType === 'super_admin') {
            // Try custom DB rate first
            if ($charge->charger_id) {
                return $this->adminCustomRate($charge->charger_id, $superAdminRate);
            }
            return $superAdminRate;
        }

        if ($chargerType === 'area_manager') {
            if ($charge->charger_id) {
                return $this->adminCustomRate($charge->charger_id, $areaManagerRate);
            }
            return $areaManagerRate;
        }

        if ($chargerType === 'agency' || $chargerType === 'host_agency') {
            return $shippingRate;
        }

        if ($chargerType === 'user' || $charge->charger_type === null) {
            return $userRate;
        }

        return $appBaseRate;
    }

    /**
     * Get admin custom rate from AdminCoinRate, with in-memory cache.
     */
    private function adminCustomRate(int $adminId, float $fallback): float
    {
        if (!isset($this->adminRateCache[$adminId])) {
            $record = AdminCoinRate::where('admin_id', $adminId)->first();
            $this->adminRateCache[$adminId] = $record ? (float) $record->rate : $fallback;
        }
        return $this->adminRateCache[$adminId];
    }
}
