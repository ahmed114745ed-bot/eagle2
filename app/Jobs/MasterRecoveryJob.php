<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterRecoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;
    public $tries = 1;

    protected $targetMonth = 3;
    protected $targetYear = 2026;
    protected $bugDate = '2026-03-01';
    protected $endDate = '2026-04-01';
    protected $thresholdDate = '2026-03-19 00:00:00';
    protected $maxCycles = 10;
    protected $reportFile = 'master_recovery_report.html';
    protected $csvFile = 'master_recovery_deductions.csv';

    protected $affectedUserIds = [];
    protected $visitedUsers = [];
    protected $traces = [];

    // Deduction log for CSV export
    protected $deductions = [];

    // Stats
    protected $stats = [
        'merge_users_affected' => 0,
        'merge_diamonds_corrected' => 0,
        'merge_duplicates_removed' => 0,
        'salary_users_calculated' => 0,
        'recovery_cycles' => 0,
        'user_debtors_total' => 0,
        'agency_debtors_total' => 0,
        'user_recovered_usd' => 0,
        'agency_recovered_usd' => 0,
        'user_unrecoverable_usd' => 0,
        'agency_unrecoverable_usd' => 0,
    ];

    public function handle()
    {
        ini_set('memory_limit', '2G');

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }
        DB::disableQueryLog();
        DB::statement("SET SESSION innodb_lock_wait_timeout = 120");

        $this->initReport();

        // ============================================
        // PHASE 1: Merge Duplicates & Fix Diamonds
        // ============================================
        $this->appendPhaseHeader(1, 'دمج المكررات وتصحيح الماس');
        Log::info("MasterRecovery: Phase 1 - Merge Duplicates");
        $this->phase1MergeDuplicates();

        // ============================================
        // PHASE 2: Calculate Salaries
        // ============================================
        $this->appendPhaseHeader(2, 'حساب الرواتب');
        Log::info("MasterRecovery: Phase 2 - Calculate Salaries");
        $this->phase2CalculateSalaries();

        // ============================================
        // PHASE 3: Recovery Cycles
        // ============================================
        $this->appendPhaseHeader(3, 'دورات الاسترداد (يوزرز + وكالات)');
        Log::info("MasterRecovery: Phase 3 - Recovery Cycles");
        $this->phase3RecoveryCycles();

        // ============================================
        // Finalize
        // ============================================
        $this->writeCsv();
        $this->finishReport();
        Log::info("MasterRecovery: Complete.");
    }

    // =========================================================================
    // PHASE 1: Merge Duplicates
    // =========================================================================
    private function phase1MergeDuplicates(): void
    {
        $startOfMonth = \Carbon\Carbon::create($this->targetYear, $this->targetMonth, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::create($this->targetYear, $this->targetMonth, 1)->endOfMonth();

        // Delete gift source_type gifts after threshold
        $deletedGifts = DB::table('gift_logs')
            ->where('source_type', 'gift')
            ->where('created_at', '>=', $this->thresholdDate)
            ->count();

        DB::table('gift_logs')
            ->where('source_type', 'gift')
            ->where('created_at', '>=', $this->thresholdDate)
            ->delete();

        $this->appendStep('حذف الهدايا المكررة', "تم حذف {$deletedGifts} هدية مكررة بعد {$this->thresholdDate}");

        $users = DB::table('users')
            ->where('agency_id', '!=', null)
            ->where('agency_id', '>', 0)
            ->get();

        $correctedCount = 0;
        $duplicatesRemoved = 0;

        foreach ($users as $user) {
            if (!$user || !$user->agency_id) continue;

            $currentAgencyId = $user->agency_id;

            $joinRequest = DB::table('agency_join_requests')
                ->where('user_id', $user->id)
                ->where('agency_id', $currentAgencyId)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            $joinTime = $joinRequest ? $joinRequest->created_at : $startOfMonth->toDateTimeString();
            $effectiveStartTime = max($joinTime, $startOfMonth->toDateTimeString());

            $realDiamonds = DB::table('gift_logs')
                ->where('receiver_id', $user->id)
                ->where('agency_id', $currentAgencyId)
                ->whereBetween('created_at', [$effectiveStartTime, $endOfMonth])
                ->sum('giftPrice');

            $totalCutAmount = DB::table('user_sallaries')
                ->where(['user_id' => $user->id, 'month' => $this->targetMonth, 'year' => $this->targetYear])
                ->where('user_agency_id', $currentAgencyId)
                ->sum('cut_amount');

            $salaryRecordsCount = DB::table('user_sallaries')
                ->where(['user_id' => $user->id, 'month' => $this->targetMonth, 'year' => $this->targetYear])
                ->where('user_agency_id', $currentAgencyId)
                ->count();

            $currentDiamond = DB::table('monthly_diamond_receives')
                ->where(['user_id' => $user->id, 'month' => $this->targetMonth, 'year' => $this->targetYear])
                ->value('monthly_diamond_received');

            $isDuplicate = $salaryRecordsCount > 1;
            $isDiamondWrong = $currentDiamond != $realDiamonds;

            if ($isDuplicate || $isDiamondWrong) {
                $correctedCount++;
                if ($isDuplicate) $duplicatesRemoved++;

                DB::table('monthly_diamond_receives')->updateOrInsert(
                    ['user_id' => $user->id, 'month' => $this->targetMonth, 'year' => $this->targetYear],
                    ['monthly_diamond_received' => $realDiamonds]
                );

                DB::table('user_sallaries')
                    ->where(['user_id' => $user->id, 'month' => $this->targetMonth, 'year' => $this->targetYear])
                    ->delete();

                DB::table('user_sallaries')->insert([
                    'user_id' => $user->id,
                    'month' => $this->targetMonth,
                    'year' => $this->targetYear,
                    'user_agency_id' => $currentAgencyId,
                    'achieved_diamond' => $realDiamonds,
                    'cut_amount' => $totalCutAmount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('users')->where('id', $user->id)->update(['salary_is_updated' => 1]);
            }
        }

        $this->stats['merge_users_affected'] = $correctedCount;
        $this->stats['merge_diamonds_corrected'] = $correctedCount;
        $this->stats['merge_duplicates_removed'] = $duplicatesRemoved;

        $this->appendStep('اكتمل الدمج', "تم تصحيح {$correctedCount} يوزر، حذف {$duplicatesRemoved} سجل راتب مكرر، حذف {$deletedGifts} هدية مكررة");
    }

    // =========================================================================
    // PHASE 2: Calculate Salaries
    // =========================================================================
    private function phase2CalculateSalaries(): void
    {
        $users = DB::table('users')
            ->where('agency_id', '!=', 0)
            ->where('type_user', '!=', 0)
            ->select('id')
            ->get();

        $count = 0;
        foreach ($users as $user) {
            try {
                $userModel = \App\Models\User::find($user->id);
                if (!$userModel) continue;

                $service = new \Modules\FixedTarget\Services\FixedTargetV2Service($userModel, $this->targetMonth, $this->targetYear);
                $service->calculateTarget();
                $count++;
            } catch (\Exception $e) {
                Log::warning("MasterRecovery: Salary calc failed for user {$user->id}: " . $e->getMessage());
            }
        }

        $this->stats['salary_users_calculated'] = $count;
        $this->appendStep('حساب الرواتب', "تم حساب رواتب {$count} يوزر");

        // Show initial state after salary calc
        $userDebtors = $this->countUserDebtors();
        $agencyDebtors = $this->countAgencyDebtors();
        $this->appendStep('الحالة الأولية', "يوزرز سالبين: {$userDebtors}، وكالات سالبة: {$agencyDebtors}");
    }

    // =========================================================================
    // PHASE 3: Recovery Cycles
    // =========================================================================
    private function phase3RecoveryCycles(): void
    {
        $zonesCoins = (int) (DB::table('settings')->where('key', 'zones_coins')->value('value') ?? 30000);

        for ($cycle = 1; $cycle <= $this->maxCycles; $cycle++) {
            $this->stats['recovery_cycles'] = $cycle;
            $this->appendCycleHeader($cycle);
            Log::info("MasterRecovery: Cycle {$cycle}");

            // --- User Recovery ---
            $userDebtors = $this->getUserDebtors();
            $this->appendStep("استرداد اليوزرز", "تم العثور على " . count($userDebtors) . " يوزر سالب");
            $this->stats['user_debtors_total'] = max($this->stats['user_debtors_total'], count($userDebtors));

            foreach ($userDebtors as $debtor) {
                $this->traces = [];
                $this->affectedUserIds = [];
                $this->visitedUsers = [];

                $this->processUserDebtor($debtor, $zonesCoins);
            }

            // --- Agency Recovery ---
            $agencyDebtors = $this->getAgencyDebtors();
            $this->appendStep("استرداد الوكالات", "تم العثور على " . count($agencyDebtors) . " وكالة سالبة");
            $this->stats['agency_debtors_total'] = max($this->stats['agency_debtors_total'], count($agencyDebtors));

            foreach ($agencyDebtors as $debtor) {
                $this->traces = [];
                $this->affectedUserIds = [];
                $this->visitedUsers = [];

                $this->processAgencyDebtor($debtor, $zonesCoins);
            }

            // --- Recalculate Salaries ---
            $this->appendStep("إعادة الحساب", "جاري إعادة حساب الرواتب...");
            $this->recalculateSalaries();
            $this->appendStep("إعادة الحساب", "تم");

            // --- Check ---
            $newUserDebtors = $this->countUserDebtors();
            $newAgencyDebtors = $this->countAgencyDebtors();
            $this->appendStep("فحص", "بعد إعادة الحساب: {$newUserDebtors} يوزر سالب، {$newAgencyDebtors} وكالة سالبة");

            if ($newUserDebtors == 0 && $newAgencyDebtors == 0) {
                $this->appendStep("تم بنجاح", "كل الرواتب نظيفة! اكتمل في {$cycle} دورة.");
                Log::info("MasterRecovery: All clean after {$cycle} cycles.");
                break;
            }

            if ($cycle == $this->maxCycles) {
                $this->appendStep("الحد الأقصى", "وصلنا للحد الأقصى {$this->maxCycles} دورات. لسه {$newUserDebtors} يوزر + {$newAgencyDebtors} وكالة سالبين.");
            }
        }
    }

    private function processUserDebtor($debtor, int $zonesCoins): void
    {
        $debtUsd = abs($debtor->total_debt);
        $debtCoins = (int) ($debtUsd * $zonesCoins);
        $remaining = $debtCoins;
        $user = DB::table('users')->where('id', $debtor->user_id)->first();
        $userName = $user->name ?? 'N/A';

        $maxRetries = 3;
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            DB::beginTransaction();
            try {
                $charges = DB::table('charges')
                    ->where('charger_id', $debtor->user_id)
                    ->where('charger_type', 'user')
                    ->whereIn('user_type', ['agency', 'user'])
                    ->where('created_at', '>=', $this->bugDate)
                    ->orderByDesc('created_at')
                    ->get();

                foreach ($charges as $charge) {
                    if ($remaining <= 0) break;

                    if ($charge->user_type === 'agency') {
                        $agency = DB::table('agencies')->where('id', $charge->user_id)->first();
                        if (!$agency) continue;

                        $canDeduct = min(min($remaining, (int) $charge->amount), (int) $agency->coins);
                        if ($canDeduct > 0 && (int) $agency->coins >= (int) $charge->amount) {
                            DB::statement("UPDATE agencies SET coins = CAST(coins AS SIGNED) - ? WHERE id = ?", [$canDeduct, $agency->id]);
                            DB::table('charges')->where('id', $charge->id)->delete();
                            $remaining -= $canDeduct;
                            $this->traces[] = ['depth' => 0, 'type' => 'agency', 'text' => "Agency #{$agency->id}: -{$canDeduct} coins, charge #{$charge->id} deleted"];

                            $this->deductions[] = [
                                'type' => 'user_recovery',
                                'debtor_id' => $debtor->user_id,
                                'debtor_name' => $userName,
                                'debtor_type' => 'user',
                                'target_id' => $agency->id,
                                'target_name' => $agency->name ?? 'N/A',
                                'target_type' => 'agency',
                                'amount_coins' => $canDeduct,
                                'amount_usd' => round($canDeduct / $zonesCoins, 4),
                                'action' => 'خصم كوينز وكالة + حذف شحنة',
                                'reason' => "يوزر #{$debtor->user_id} شحن وكالة #{$agency->id} بكوينز مخالفة",
                            ];
                        }
                    } else {
                        $userB = DB::table('users')->where('id', $charge->user_id)->first();
                        if (!$userB) continue;

                        $chargeAmount = min($remaining, (int) $charge->amount);

                        DB::table('charges')->where('id', $charge->id)->delete();
                        $this->traces[] = ['depth' => 0, 'type' => 'charge', 'text' => "Charge #{$charge->id} deleted (Debtor &rarr; User #{$userB->id})"];

                        $canDeduct = min($chargeAmount, (int) $userB->di);
                        if ($canDeduct > 0) {
                            DB::statement("UPDATE users SET di = CAST(di AS SIGNED) - ? WHERE id = ?", [$canDeduct, $userB->id]);
                            $remaining -= $canDeduct;
                            $chargeAmount -= $canDeduct;
                            $this->traces[] = ['depth' => 0, 'type' => 'di', 'text' => "User #{$userB->id} di: -{$canDeduct}"];

                            $this->deductions[] = [
                                'type' => 'user_recovery',
                                'debtor_id' => $debtor->user_id,
                                'debtor_name' => $userName,
                                'debtor_type' => 'user',
                                'target_id' => $userB->id,
                                'target_name' => $userB->name ?? 'N/A',
                                'target_type' => 'user',
                                'amount_coins' => $canDeduct,
                                'amount_usd' => round($canDeduct / $zonesCoins, 4),
                                'action' => 'خصم رصيد + حذف شحنة',
                                'reason' => "يوزر #{$debtor->user_id} شحن يوزر #{$userB->id}، استرداد من رصيد الماس",
                            ];
                        }

                        if ($chargeAmount > 0) {
                            $recovered = $this->traceGifts($userB->id, $chargeAmount, 1, $debtor->user_id, $userName, 'user', $zonesCoins);
                            $remaining -= $recovered;
                        }
                    }
                }

                $recoveredCoins = $debtCoins - $remaining;
                $recoveredUsd = $recoveredCoins / $zonesCoins;

                if ($recoveredCoins > 0) {
                    $salaryRecord = DB::table('user_sallaries')
                        ->where('user_id', $debtor->user_id)
                        ->where('month', $this->targetMonth)
                        ->where('year', $this->targetYear)
                        ->orderByDesc('cut_amount')
                        ->first();

                    if ($salaryRecord) {
                        $newCut = max(0, $salaryRecord->cut_amount - $recoveredUsd);
                        DB::table('user_sallaries')
                            ->where('id', $salaryRecord->id)
                            ->update(['cut_amount' => $newCut]);
                    }
                }

                $this->affectedUserIds[] = $debtor->user_id;
                $affectedUserIds = array_unique($this->affectedUserIds);
                if (!empty($affectedUserIds)) {
                    foreach (array_chunk($affectedUserIds, 500) as $chunk) {
                        DB::table('users')->whereIn('id', $chunk)->update(['salary_is_updated' => 0]);
                    }
                }

                if ($remaining > 0) {
                    $salaryRecord = $salaryRecord ?? DB::table('user_sallaries')
                        ->where('user_id', $debtor->user_id)
                        ->where('month', $this->targetMonth)
                        ->where('year', $this->targetYear)
                        ->orderByDesc('cut_amount')
                        ->first();

                    if ($salaryRecord) {
                        DB::table('user_sallaries')
                            ->where('id', $salaryRecord->id)
                            ->update(['cut_amount' => $salaryRecord->sallary]);
                    }
                }

                DB::commit();

                $this->stats['user_recovered_usd'] += $recoveredUsd;
                if ($remaining > 0) {
                    $this->stats['user_unrecoverable_usd'] += $remaining / $zonesCoins;
                }

                $status = $remaining > 0 ? 'partial' : 'recovered';
                $this->appendDebtorCard('user', $debtor->user_id, $userName, $debtUsd, $recoveredUsd, $remaining / $zonesCoins, $status);

                break;

            } catch (\Exception $e) {
                DB::rollBack();

                if ($attempt < $maxRetries && str_contains($e->getMessage(), 'Deadlock')) {
                    Log::warning("MasterRecovery: Deadlock for user {$debtor->user_id}, retry {$attempt}");
                    $this->traces = [];
                    $this->affectedUserIds = [];
                    $this->visitedUsers = [];
                    sleep(2);
                    continue;
                }

                $this->appendDebtorCard('user', $debtor->user_id, $userName, $debtUsd, 0, $debtUsd, 'error', $e->getMessage());
                Log::error("MasterRecovery: Failed user {$debtor->user_id}: " . $e->getMessage());

                try {
                    $salaryRecord = DB::table('user_sallaries')
                        ->where('user_id', $debtor->user_id)
                        ->where('month', $this->targetMonth)
                        ->where('year', $this->targetYear)
                        ->orderByDesc('cut_amount')
                        ->first();
                    if ($salaryRecord) {
                        DB::table('user_sallaries')
                            ->where('id', $salaryRecord->id)
                            ->update(['cut_amount' => $salaryRecord->sallary]);
                    }
                } catch (\Exception $ex) {}

                break;
            }
        }
    }

    private function processAgencyDebtor($debtor, int $zonesCoins): void
    {
        $debtUsd = abs($debtor->total_debt);
        $debtCoins = (int) ($debtUsd * $zonesCoins);
        $remaining = $debtCoins;
        $agency = DB::table('agencies')->where('id', $debtor->agency_id)->first();
        $agencyName = $agency->name ?? 'N/A';

        $maxRetries = 3;
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            DB::beginTransaction();
            try {
                $charges = DB::table('charges')
                    ->where('charger_id', $debtor->agency_id)
                    ->where('charger_type', 'host_agency')
                    ->whereIn('user_type', ['agency', 'user'])
                    ->where('created_at', '>=', $this->bugDate)
                    ->orderByDesc('created_at')
                    ->get();

                foreach ($charges as $charge) {
                    if ($remaining <= 0) break;

                    if ($charge->user_type === 'agency') {
                        $targetAgency = DB::table('agencies')->where('id', $charge->user_id)->first();
                        if (!$targetAgency) continue;

                        $canDeduct = min(min($remaining, (int) $charge->amount), (int) $targetAgency->coins);
                        if ($canDeduct > 0 && (int) $targetAgency->coins >= (int) $charge->amount) {
                            DB::statement("UPDATE agencies SET coins = CAST(coins AS SIGNED) - ? WHERE id = ?", [$canDeduct, $targetAgency->id]);
                            DB::table('charges')->where('id', $charge->id)->delete();
                            $remaining -= $canDeduct;
                            $this->traces[] = ['depth' => 0, 'type' => 'agency', 'text' => "Agency #{$targetAgency->id}: -{$canDeduct} coins, charge #{$charge->id} deleted"];

                            $this->deductions[] = [
                                'type' => 'agency_recovery',
                                'debtor_id' => $debtor->agency_id,
                                'debtor_name' => $agencyName,
                                'debtor_type' => 'agency',
                                'target_id' => $targetAgency->id,
                                'target_name' => $targetAgency->name ?? 'N/A',
                                'target_type' => 'agency',
                                'amount_coins' => $canDeduct,
                                'amount_usd' => round($canDeduct / $zonesCoins, 4),
                                'action' => 'خصم كوينز وكالة + حذف شحنة',
                                'reason' => "وكالة #{$debtor->agency_id} شحنت وكالة #{$targetAgency->id} بكوينز مخالفة",
                            ];
                        }
                    } else {
                        $userB = DB::table('users')->where('id', $charge->user_id)->first();
                        if (!$userB) continue;

                        $chargeAmount = min($remaining, (int) $charge->amount);

                        DB::table('charges')->where('id', $charge->id)->delete();
                        $this->traces[] = ['depth' => 0, 'type' => 'charge', 'text' => "Charge #{$charge->id} deleted (Agency &rarr; User #{$userB->id})"];

                        $canDeduct = min($chargeAmount, (int) $userB->di);
                        if ($canDeduct > 0) {
                            DB::statement("UPDATE users SET di = CAST(di AS SIGNED) - ? WHERE id = ?", [$canDeduct, $userB->id]);
                            $remaining -= $canDeduct;
                            $chargeAmount -= $canDeduct;
                            $this->traces[] = ['depth' => 0, 'type' => 'di', 'text' => "User #{$userB->id} di: -{$canDeduct}"];

                            $this->deductions[] = [
                                'type' => 'agency_recovery',
                                'debtor_id' => $debtor->agency_id,
                                'debtor_name' => $agencyName,
                                'debtor_type' => 'agency',
                                'target_id' => $userB->id,
                                'target_name' => $userB->name ?? 'N/A',
                                'target_type' => 'user',
                                'amount_coins' => $canDeduct,
                                'amount_usd' => round($canDeduct / $zonesCoins, 4),
                                'action' => 'خصم رصيد + حذف شحنة',
                                'reason' => "وكالة #{$debtor->agency_id} شحنت يوزر #{$userB->id}، استرداد من رصيد الماس",
                            ];
                        }

                        if ($chargeAmount > 0) {
                            $recovered = $this->traceGifts($userB->id, $chargeAmount, 1, $debtor->agency_id, $agencyName, 'agency', $zonesCoins);
                            $remaining -= $recovered;
                        }
                    }
                }

                $recoveredCoins = $debtCoins - $remaining;
                $recoveredUsd = $recoveredCoins / $zonesCoins;

                if ($recoveredCoins > 0) {
                    $salaryRecord = DB::table('agency_sallaries')
                        ->where('agency_id', $debtor->agency_id)
                        ->where('month', $this->targetMonth)
                        ->where('year', $this->targetYear)
                        ->orderByDesc('cut_amount')
                        ->first();

                    if ($salaryRecord) {
                        $newCut = max(0, $salaryRecord->cut_amount - $recoveredUsd);
                        DB::table('agency_sallaries')
                            ->where('id', $salaryRecord->id)
                            ->update(['cut_amount' => $newCut]);
                    }
                }

                $affectedUserIds = array_unique($this->affectedUserIds);
                if (!empty($affectedUserIds)) {
                    foreach (array_chunk($affectedUserIds, 500) as $chunk) {
                        DB::table('users')->whereIn('id', $chunk)->update(['salary_is_updated' => 0]);
                    }
                }

                if ($remaining > 0) {
                    $salaryRecord = $salaryRecord ?? DB::table('agency_sallaries')
                        ->where('agency_id', $debtor->agency_id)
                        ->where('month', $this->targetMonth)
                        ->where('year', $this->targetYear)
                        ->orderByDesc('cut_amount')
                        ->first();

                    if ($salaryRecord) {
                        DB::table('agency_sallaries')
                            ->where('id', $salaryRecord->id)
                            ->update(['cut_amount' => $salaryRecord->sallary]);
                    }
                }

                DB::commit();

                $this->stats['agency_recovered_usd'] += $recoveredUsd;
                if ($remaining > 0) {
                    $this->stats['agency_unrecoverable_usd'] += $remaining / $zonesCoins;
                }

                $status = $remaining > 0 ? 'partial' : 'recovered';
                $this->appendDebtorCard('agency', $debtor->agency_id, $agencyName, $debtUsd, $recoveredUsd, $remaining / $zonesCoins, $status);

                break;

            } catch (\Exception $e) {
                DB::rollBack();

                if ($attempt < $maxRetries && str_contains($e->getMessage(), 'Deadlock')) {
                    Log::warning("MasterRecovery: Deadlock for agency {$debtor->agency_id}, retry {$attempt}");
                    $this->traces = [];
                    $this->affectedUserIds = [];
                    $this->visitedUsers = [];
                    sleep(2);
                    continue;
                }

                $this->appendDebtorCard('agency', $debtor->agency_id, $agencyName, $debtUsd, 0, $debtUsd, 'error', $e->getMessage());
                Log::error("MasterRecovery: Failed agency {$debtor->agency_id}: " . $e->getMessage());

                try {
                    $salaryRecord = DB::table('agency_sallaries')
                        ->where('agency_id', $debtor->agency_id)
                        ->where('month', $this->targetMonth)
                        ->where('year', $this->targetYear)
                        ->orderByDesc('cut_amount')
                        ->first();
                    if ($salaryRecord) {
                        DB::table('agency_sallaries')
                            ->where('id', $salaryRecord->id)
                            ->update(['cut_amount' => $salaryRecord->sallary]);
                    }
                } catch (\Exception $ex) {}

                break;
            }
        }
    }

    private function traceGifts(int $senderId, int $amount, int $depth, $debtorId, string $debtorName, string $debtorType, int $zonesCoins): int
    {
        if ($amount <= 0 || $depth > 10) return 0;
        if (in_array($senderId, $this->visitedUsers)) return 0;
        $this->visitedUsers[] = $senderId;

        $totalRecovered = 0;

        $gifts = DB::select("
            SELECT receiver_id, SUM(giftPrice) as total_sent
            FROM gift_logs
            WHERE sender_id = ? AND created_at >= ? AND created_at < ?
            GROUP BY receiver_id
            ORDER BY total_sent DESC
            LIMIT 20
        ", [$senderId, $this->bugDate, $this->endDate]);

        foreach ($gifts as $gift) {
            if ($amount <= 0) break;

            $receiver = DB::table('users')->where('id', $gift->receiver_id)->first();
            if (!$receiver) continue;

            $giftAmount = min($amount, (int) $gift->total_sent);

            $remainingToDelete = $giftAmount;
            $logsToDelete = [];
            $giftLogRows = DB::table('gift_logs')
                ->where('sender_id', $senderId)
                ->where('receiver_id', $receiver->id)
                ->where('created_at', '>=', $this->bugDate)
                ->where('created_at', '<', $this->endDate)
                ->orderBy('giftPrice')
                ->select('id', 'giftPrice', 'room_id', 'receiver_family_id')
                ->get();

            $deletedAmount = 0;
            $roomAmounts = [];
            $familyAmounts = [];
            foreach ($giftLogRows as $row) {
                if ($remainingToDelete <= 0) break;
                $logsToDelete[] = $row->id;
                $price = (int) $row->giftPrice;
                $deletedAmount += $price;
                $remainingToDelete -= $price;

                if ($row->room_id) {
                    $roomAmounts[$row->room_id] = ($roomAmounts[$row->room_id] ?? 0) + $price;
                }
                if ($row->receiver_family_id) {
                    $familyAmounts[$row->receiver_family_id] = ($familyAmounts[$row->receiver_family_id] ?? 0) + $price;
                }
            }

            if ($deletedAmount <= 0) continue;

            if (!empty($logsToDelete)) {
                foreach (array_chunk($logsToDelete, 500) as $chunk) {
                    DB::table('gift_logs')->whereIn('id', $chunk)->delete();
                }
            }

            DB::statement("UPDATE users SET total_diamond_received = GREATEST(0, CAST(total_diamond_received AS SIGNED) - ?) WHERE id = ?", [$deletedAmount, $receiver->id]);
            DB::statement("UPDATE users SET exchange_diamonds = GREATEST(0, CAST(exchange_diamonds AS SIGNED) - ?) WHERE id = ? AND agency_id = 0", [$deletedAmount, $receiver->id]);
            DB::statement("UPDATE monthly_diamond_receives SET monthly_diamond_received = GREATEST(0, CAST(monthly_diamond_received AS SIGNED) - ?) WHERE user_id = ? AND month = ? AND year = ?", [$deletedAmount, $receiver->id, $this->targetMonth, $this->targetYear]);
            DB::statement("UPDATE users SET monthly_diamond_send = GREATEST(0, CAST(monthly_diamond_send AS SIGNED) - ?) WHERE id = ?", [$deletedAmount, $senderId]);

            foreach ($roomAmounts as $roomId => $roomAmount) {
                DB::statement("UPDATE rooms SET session = GREATEST(0, CAST(session AS SIGNED) - ?) WHERE id = ?", [$roomAmount, $roomId]);
                DB::statement("UPDATE room_top_users SET coins = GREATEST(0, CAST(coins AS SIGNED) - ?) WHERE room_id = ? AND user_id = ?", [$roomAmount, $roomId, $senderId]);
            }

            foreach ($familyAmounts as $familyId => $famAmount) {
                DB::statement("UPDATE families SET total_diamond = GREATEST(0, CAST(total_diamond AS SIGNED) - ?) WHERE id = ?", [$famAmount, $familyId]);
            }

            $this->affectedUserIds[] = $receiver->id;
            $this->affectedUserIds[] = $senderId;

            $this->traces[] = ['depth' => $depth, 'type' => 'gift', 'text' => "#{$senderId} &rarr; #{$receiver->id}: -{$deletedAmount} coins"];

            $this->deductions[] = [
                'type' => $debtorType . '_recovery',
                'debtor_id' => $debtorId,
                'debtor_name' => $debtorName,
                'debtor_type' => $debtorType,
                'target_id' => $receiver->id,
                'target_name' => $receiver->name ?? 'N/A',
                'target_type' => 'user',
                'amount_coins' => $deletedAmount,
                'amount_usd' => round($deletedAmount / $zonesCoins, 4),
                'action' => 'حذف هدايا + عكس أرصدة (عمق=' . $depth . ')',
                'reason' => "سلسلة هدايا: #{$senderId} أرسل لـ #{$receiver->id}، تتبع من المدين #{$debtorId}",
            ];

            $amount -= $deletedAmount;
            $totalRecovered += $deletedAmount;

            if ($amount > 0) {
                $subRecovered = $this->traceGifts($receiver->id, $amount, $depth + 1, $debtorId, $debtorName, $debtorType, $zonesCoins);
                $amount -= $subRecovered;
                $totalRecovered += $subRecovered;
            }
        }

        return $totalRecovered;
    }

    // =========================================================================
    // Helpers
    // =========================================================================
    private function getUserDebtors(): array
    {
        return DB::select("
            SELECT user_id,
                   SUM(sallary) as total_earned,
                   SUM(cut_amount) as total_cut,
                   SUM(sallary) - SUM(cut_amount) as total_debt
            FROM user_sallaries
            WHERE month = ? AND year = ?
            GROUP BY user_id
            HAVING total_debt < 0
            ORDER BY total_debt ASC
        ", [$this->targetMonth, $this->targetYear]);
    }

    private function getAgencyDebtors(): array
    {
        return DB::select("
            SELECT agency_id,
                   SUM(sallary) as total_earned,
                   SUM(cut_amount) as total_cut,
                   SUM(sallary) - SUM(cut_amount) as total_debt
            FROM agency_sallaries
            WHERE month = ? AND year = ?
            GROUP BY agency_id
            HAVING total_debt < 0
            ORDER BY total_debt ASC
        ", [$this->targetMonth, $this->targetYear]);
    }

    private function countUserDebtors(): int
    {
        $result = DB::selectOne("
            SELECT COUNT(*) as cnt FROM (
                SELECT user_id FROM user_sallaries
                WHERE month = ? AND year = ?
                GROUP BY user_id
                HAVING SUM(sallary) - SUM(cut_amount) < 0
            ) t
        ", [$this->targetMonth, $this->targetYear]);
        return $result->cnt ?? 0;
    }

    private function countAgencyDebtors(): int
    {
        $result = DB::selectOne("
            SELECT COUNT(*) as cnt FROM (
                SELECT agency_id FROM agency_sallaries
                WHERE month = ? AND year = ?
                GROUP BY agency_id
                HAVING SUM(sallary) - SUM(cut_amount) < 0
            ) t
        ", [$this->targetMonth, $this->targetYear]);
        return $result->cnt ?? 0;
    }

    private function recalculateSalaries(): void
    {
        $users = DB::table('users')
            ->where('agency_id', '!=', 0)
            ->where('type_user', '!=', 0)
            ->select('id')
            ->get();

        foreach ($users as $user) {
            try {
                $userModel = \App\Models\User::find($user->id);
                if (!$userModel) continue;

                $service = new \Modules\FixedTarget\Services\FixedTargetV2Service($userModel, $this->targetMonth, $this->targetYear);
                $service->calculateTarget();
            } catch (\Exception $e) {
                Log::warning("MasterRecovery: Recalc failed for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    // =========================================================================
    // CSV Export
    // =========================================================================
    private function writeCsv(): void
    {
        $path = public_path($this->csvFile);
        $fp = fopen($path, 'w');

        // BOM for Excel Arabic support
        fwrite($fp, "\xEF\xBB\xBF");

        fputcsv($fp, [
            'النوع', 'رقم المدين', 'اسم المدين', 'نوع المدين',
            'رقم المخصوم منه', 'اسم المخصوم منه', 'نوع المخصوم منه',
            'المبلغ (كوينز)', 'المبلغ (دولار)', 'العملية', 'السبب'
        ]);

        foreach ($this->deductions as $d) {
            fputcsv($fp, [
                $d['type'], $d['debtor_id'], $d['debtor_name'], $d['debtor_type'],
                $d['target_id'], $d['target_name'], $d['target_type'],
                $d['amount_coins'], $d['amount_usd'], $d['action'], $d['reason'],
            ]);
        }

        fclose($fp);
    }

    // =========================================================================
    // HTML Report
    // =========================================================================
    private function initReport(): void
    {
        $path = public_path($this->reportFile);
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تقرير الاسترداد الشامل</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #0f172a; color: #e2e8f0; padding: 20px; direction: rtl; }
.header { text-align: center; padding: 30px; background: linear-gradient(135deg, #1e293b, #334155); border-radius: 16px; margin-bottom: 30px; border: 1px solid #475569; }
.header h1 { font-size: 28px; color: #a78bfa; margin-bottom: 8px; }
.header .date { color: #94a3b8; font-size: 14px; }
.phase { background: #1e293b; border-radius: 12px; margin-bottom: 24px; border: 1px solid #334155; padding: 20px; }
.phase-title { font-size: 22px; font-weight: 700; color: #c084fc; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #334155; display: flex; align-items: center; gap: 10px; }
.phase-number { background: #7c3aed; color: white; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; }
.cycle { background: #162032; border-radius: 8px; margin: 12px 0; border: 1px solid #1e3a5f; padding: 16px; }
.cycle-title { font-size: 16px; font-weight: 600; color: #38bdf8; margin-bottom: 12px; }
.step { padding: 6px 14px; margin-bottom: 4px; border-radius: 6px; font-size: 13px; display: flex; align-items: center; gap: 8px; }
.step-normal { background: #1a2332; border-left: 3px solid #38bdf8; }
.step-error { background: #2a1215; border-left: 3px solid #f87171; }
.step-success { background: #132a1e; border-left: 3px solid #4ade80; }
.step { border-left: none; }
.step-normal { border-right: 3px solid #38bdf8; border-left: none; }
.step-error { border-right: 3px solid #f87171; border-left: none; }
.step-success { border-right: 3px solid #4ade80; border-left: none; }
.step-label { font-weight: 600; min-width: 140px; color: #94a3b8; }
.step-text { color: #e2e8f0; }
.debtor-card { background: #1a2332; border-radius: 8px; margin: 8px 0; border: 1px solid #2d3748; overflow: hidden; }
.debtor-header { padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: background 0.2s; }
.debtor-header:hover { background: #2d3748; }
.debtor-info { display: flex; align-items: center; gap: 10px; }
.debtor-id { background: #475569; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-family: monospace; }
.debtor-name { font-weight: 600; font-size: 14px; }
.badge { padding: 3px 10px; border-radius: 16px; font-size: 11px; font-weight: 600; }
.badge-recovered { background: #065f46; color: #6ee7b7; }
.badge-partial { background: #78350f; color: #fcd34d; }
.badge-error { background: #7f1d1d; color: #fca5a5; }
.amounts { display: flex; gap: 16px; font-size: 12px; color: #94a3b8; }
.amounts span { display: flex; align-items: center; gap: 3px; }
.amount-label { color: #64748b; }
.traces { padding: 0 16px 12px; display: none; }
.traces.open { display: block; }
.trace { padding: 3px 0; font-size: 12px; font-family: monospace; color: #94a3b8; border-right: 2px solid #334155; margin-right: 6px; padding-right: 10px; border-left: none; margin-left: 0; padding-left: 0; }
.trace.depth-0 { border-right-color: #38bdf8; color: #7dd3fc; }
.trace.depth-1 { margin-right: 20px; border-right-color: #a78bfa; color: #c4b5fd; }
.trace.depth-2 { margin-right: 36px; border-right-color: #fb923c; color: #fdba74; }
.trace.depth-3 { margin-right: 52px; border-right-color: #4ade80; color: #86efac; }
.trace.depth-4,.trace.depth-5,.trace.depth-6,.trace.depth-7,.trace.depth-8,.trace.depth-9,.trace.depth-10 { margin-right: 68px; border-right-color: #f472b6; color: #f9a8d4; }
.toggle-arrow { transition: transform 0.2s; font-size: 14px; color: #64748b; }
.toggle-arrow.open { transform: rotate(90deg); }
.error-msg { color: #fca5a5; font-size: 11px; padding: 6px 16px; background: #450a0a; margin: 0 16px 12px; border-radius: 4px; }
.summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin: 20px 0; }
.stat-card { background: #1e293b; border-radius: 10px; padding: 16px; text-align: center; border: 1px solid #334155; }
.stat-value { font-size: 28px; font-weight: 700; margin-bottom: 2px; }
.stat-label { font-size: 12px; color: #94a3b8; }
.csv-link { display: inline-block; margin: 20px 0; padding: 12px 24px; background: #7c3aed; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
.csv-link:hover { background: #6d28d9; }
.final-table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 10px; overflow: hidden; margin-top: 20px; }
.final-table th { background: #334155; padding: 10px 14px; text-align: right; font-size: 12px; color: #94a3b8; }
.final-table td { padding: 8px 14px; border-top: 1px solid #334155; font-size: 13px; }
.final-table tr:hover td { background: #2d3748; }
</style>
</head>
<body>
<div class="header">
<h1>تقرير الاسترداد الشامل</h1>
<div class="date">بدأ: REPORT_DATE</div>
</div>
<div id="content">
HTML;
        $html = str_replace('REPORT_DATE', now()->toDateTimeString(), $html);
        file_put_contents($path, $html);
    }

    private function appendPhaseHeader(int $num, string $title): void
    {
        $path = public_path($this->reportFile);
        $html = "<div class=\"phase\"><div class=\"phase-title\"><span class=\"phase-number\">{$num}</span>{$title}</div>\n";
        file_put_contents($path, $html, FILE_APPEND);
    }

    private function appendCycleHeader(int $cycle): void
    {
        $path = public_path($this->reportFile);
        $html = "<div class=\"cycle\"><div class=\"cycle-title\">الدورة #{$cycle} - " . now()->toDateTimeString() . "</div>\n";
        file_put_contents($path, $html, FILE_APPEND);
    }

    private function appendStep(string $label, string $text, bool $isError = false): void
    {
        $path = public_path($this->reportFile);
        $class = $isError ? 'step-error' : (str_contains($label, 'تم بنجاح') ? 'step-success' : 'step-normal');
        $label = htmlspecialchars($label);
        $text = htmlspecialchars(substr($text, 0, 500));
        $html = "<div class=\"step {$class}\"><span class=\"step-label\">{$label}</span><span class=\"step-text\">{$text}</span></div>\n";
        file_put_contents($path, $html, FILE_APPEND);
    }

    private function appendDebtorCard(string $entityType, int $id, string $name, float $debt, float $recovered, float $stillNegative, string $status, string $errorMsg = ''): void
    {
        $path = public_path($this->reportFile);

        $badgeClass = match($status) {
            'recovered' => 'badge-recovered',
            'partial' => 'badge-partial',
            'error' => 'badge-error',
            default => 'badge-partial',
        };
        $badgeText = match($status) {
            'recovered' => 'تم الاسترداد',
            'partial' => 'لسه سالب',
            'error' => 'خطأ',
            default => $status,
        };

        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $prefix = $entityType === 'agency' ? 'وكالة' : 'يوزر';
        $cardId = "{$entityType}-{$id}-" . time();

        $tracesHtml = '';
        foreach ($this->traces as $t) {
            $depthClass = "depth-{$t['depth']}";
            $icon = match($t['type']) {
                'agency' => '&#127970;',
                'charge' => '&#128465;',
                'di' => '&#128176;',
                'gift' => '&#127873;',
                default => '&#8226;',
            };
            $tracesHtml .= "<div class=\"trace {$depthClass}\"><span>{$icon}</span> {$t['text']}</div>\n";
        }

        $errorHtml = $errorMsg ? '<div class="error-msg">' . htmlspecialchars(substr($errorMsg, 0, 300)) . '</div>' : '';
        $stillNegSpan = $stillNegative > 0 ? "<span><span class=\"amount-label\">المتبقي:</span> \$" . round($stillNegative, 2) . "</span>" : '';

        $html = <<<HTML
<div class="debtor-card">
<div class="debtor-header" onclick="var t=document.getElementById('{$cardId}');var a=this.querySelector('.toggle-arrow');t.classList.toggle('open');a.classList.toggle('open');">
<div class="debtor-info">
<span class="toggle-arrow">&#9654;</span>
<span class="debtor-id">{$prefix} #{$id}</span>
<span class="debtor-name">{$name}</span>
</div>
<div style="display:flex;align-items:center;gap:12px;">
<div class="amounts">
<span><span class="amount-label">الدين:</span> \$DEBT_VAL</span>
<span><span class="amount-label">تم استرداده:</span> \$RECOVERED_VAL</span>
{$stillNegSpan}
</div>
<span class="badge {$badgeClass}">{$badgeText}</span>
</div>
</div>
{$errorHtml}
<div class="traces" id="{$cardId}">
{$tracesHtml}
</div>
</div>
HTML;

        $html = str_replace('DEBT_VAL', round($debt, 2), $html);
        $html = str_replace('RECOVERED_VAL', round($recovered, 2), $html);

        file_put_contents($path, $html . "\n", FILE_APPEND);
    }

    private function finishReport(): void
    {
        $path = public_path($this->reportFile);
        $zonesCoins = (int) (DB::table('settings')->where('key', 'zones_coins')->value('value') ?? 30000);

        $userDebtors = $this->countUserDebtors();
        $agencyDebtors = $this->countAgencyDebtors();

        // Final salary totals
        $userSalaryTotal = DB::selectOne("SELECT SUM(sallary) as s, SUM(cut_amount) as c, SUM(sallary)-SUM(cut_amount) as net FROM user_sallaries WHERE month=? AND year=?", [$this->targetMonth, $this->targetYear]);
        $agencySalaryTotal = DB::selectOne("SELECT SUM(sallary) as s, SUM(cut_amount) as c, SUM(sallary)-SUM(cut_amount) as net FROM agency_sallaries WHERE month=? AND year=?", [$this->targetMonth, $this->targetYear]);

        $totalNetSalary = round(($userSalaryTotal->net ?? 0) + ($agencySalaryTotal->net ?? 0), 2);
        $userNet = round($userSalaryTotal->net ?? 0, 2);
        $agencyNet = round($agencySalaryTotal->net ?? 0, 2);

        // =============================================
        // Build per-entity detailed profiles
        // =============================================
        $entityProfiles = $this->buildEntityProfiles($zonesCoins);

        // Build deductions table HTML
        $tableRows = '';
        foreach ($this->deductions as $i => $d) {
            $rowNum = $i + 1;
            $tableRows .= "<tr>
                <td>{$rowNum}</td>
                <td>{$d['debtor_type']}</td>
                <td>#{$d['debtor_id']} - " . htmlspecialchars($d['debtor_name']) . "</td>
                <td>#{$d['target_id']} - " . htmlspecialchars($d['target_name']) . " ({$d['target_type']})</td>
                <td>" . number_format($d['amount_coins']) . "</td>
                <td>\${$d['amount_usd']}</td>
                <td>{$d['action']}</td>
            </tr>\n";
        }

        $totalDeductions = count($this->deductions);
        $totalDeductedCoins = array_sum(array_column($this->deductions, 'amount_coins'));
        $totalDeductedUsd = round(array_sum(array_column($this->deductions, 'amount_usd')), 2);

        $html = <<<HTML
</div></div></div>
<div class="header" style="margin-top: 40px; margin-bottom: 20px;">
<h1>الملخص النهائي</h1>
<div class="date">اكتمل: FINISH_TIME</div>
</div>

<div class="summary-grid">
<div class="stat-card"><div class="stat-value" style="color:#a78bfa;">{$this->stats['recovery_cycles']}</div><div class="stat-label">عدد الدورات</div></div>
<div class="stat-card"><div class="stat-value" style="color:#38bdf8;">{$this->stats['merge_users_affected']}</div><div class="stat-label">يوزرز تم تصحيحهم</div></div>
<div class="stat-card"><div class="stat-value" style="color:#38bdf8;">{$this->stats['salary_users_calculated']}</div><div class="stat-label">رواتب تم حسابها</div></div>
<div class="stat-card"><div class="stat-value" style="color:#f87171;">{$this->stats['user_debtors_total']}</div><div class="stat-label">أقصى عدد يوزرز سالبين</div></div>
<div class="stat-card"><div class="stat-value" style="color:#f87171;">{$this->stats['agency_debtors_total']}</div><div class="stat-label">أقصى عدد وكالات سالبة</div></div>
<div class="stat-card"><div class="stat-value" style="color:#4ade80;">\$USER_RECOVERED</div><div class="stat-label">مسترد من اليوزرز</div></div>
<div class="stat-card"><div class="stat-value" style="color:#4ade80;">\$AGENCY_RECOVERED</div><div class="stat-label">مسترد من الوكالات</div></div>
<div class="stat-card"><div class="stat-value" style="color:#fbbf24;">{$userDebtors}</div><div class="stat-label">يوزرز سالبين متبقيين</div></div>
<div class="stat-card"><div class="stat-value" style="color:#fbbf24;">{$agencyDebtors}</div><div class="stat-label">وكالات سالبة متبقية</div></div>
<div class="stat-card"><div class="stat-value" style="color:#e879f9;">\${$userNet}</div><div class="stat-label">إجمالي رواتب اليوزرز (صافي)</div></div>
<div class="stat-card"><div class="stat-value" style="color:#e879f9;">\${$agencyNet}</div><div class="stat-label">إجمالي رواتب الوكالات (صافي)</div></div>
<div class="stat-card"><div class="stat-value" style="color:#c084fc;">\${$totalNetSalary}</div><div class="stat-label">إجمالي ما يدفعه التطبيق</div></div>
</div>

<a href="/master_recovery_deductions.csv" class="csv-link" download>تحميل شيت الخصومات كامل (CSV)</a>

<h2 style="color:#a78bfa; margin: 30px 0 16px; font-size: 22px;">ملخص تفصيلي لكل مدين</h2>
{$entityProfiles}

<h2 style="color:#a78bfa; margin: 30px 0 16px; font-size: 20px;">كل الخصومات ({$totalDeductions} عملية، {$totalDeductedCoins} كوينز = \${$totalDeductedUsd})</h2>
<div style="overflow-x:auto;">
<table class="final-table">
<thead>
<tr>
<th>#</th>
<th>المصدر</th>
<th>المدين</th>
<th>المخصوم منه</th>
<th>كوينز</th>
<th>دولار</th>
<th>العملية</th>
</tr>
</thead>
<tbody>
{$tableRows}
</tbody>
</table>
</div>

</body>
</html>
HTML;

        $html = str_replace('FINISH_TIME', now()->toDateTimeString(), $html);
        $html = str_replace('USER_RECOVERED', round($this->stats['user_recovered_usd'], 2), $html);
        $html = str_replace('AGENCY_RECOVERED', round($this->stats['agency_recovered_usd'], 2), $html);

        file_put_contents($path, $html, FILE_APPEND);
    }

    private function buildEntityProfiles(int $zonesCoins): string
    {
        // Collect all unique entities involved
        $entities = [];

        foreach ($this->deductions as $d) {
            // Track as debtor
            $dKey = $d['debtor_type'] . '_' . $d['debtor_id'];
            if (!isset($entities[$dKey])) {
                $entities[$dKey] = [
                    'id' => $d['debtor_id'],
                    'name' => $d['debtor_name'],
                    'type' => $d['debtor_type'],
                    'as_debtor' => [],
                    'as_target' => [],
                ];
            }
            $entities[$dKey]['as_debtor'][] = $d;

            // Track as target
            $tKey = $d['target_type'] . '_' . $d['target_id'];
            if (!isset($entities[$tKey])) {
                $entities[$tKey] = [
                    'id' => $d['target_id'],
                    'name' => $d['target_name'],
                    'type' => $d['target_type'],
                    'as_debtor' => [],
                    'as_target' => [],
                ];
            }
            $entities[$tKey]['as_target'][] = $d;
        }

        // Only show entities that were debtors (had negative salary)
        $html = '';
        foreach ($entities as $key => $entity) {
            if (empty($entity['as_debtor'])) continue;

            $isUser = ($entity['type'] === 'user' || $entity['type'] === 'يوزر');
            $entityLabel = $isUser ? 'يوزر' : 'وكالة';
            $eName = htmlspecialchars($entity['name']);
            $eId = $entity['id'];

            // Get salary info
            $salaryInfo = '';
            if ($isUser) {
                $sal = DB::selectOne("SELECT SUM(sallary) as s, SUM(cut_amount) as c FROM user_sallaries WHERE user_id=? AND month=? AND year=?", [$eId, $this->targetMonth, $this->targetYear]);
                $user = DB::table('users')->where('id', $eId)->first(['agency_id', 'di']);
                $agencyName = '';
                if ($user && $user->agency_id) {
                    $ag = DB::table('agencies')->where('id', $user->agency_id)->first(['name']);
                    $agencyName = $ag ? htmlspecialchars($ag->name) : '';
                }
                $salary = round($sal->s ?? 0, 2);
                $cut = round($sal->c ?? 0, 2);
                $net = round($salary - $cut, 2);
                $di = $user->di ?? 0;
                $agencyId = $user->agency_id ?? 0;

                $statusColor = $net >= 0 ? '#4ade80' : '#f87171';
                $statusText = $net > 0 ? "هياخد \${$net}" : ($net == 0 ? 'لا له ولا عليه' : "عليه \$" . abs($net));

                $salaryInfo = "<div style='display:flex;gap:16px;flex-wrap:wrap;margin:12px 0;'>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;'>الوكالة: <b>#{$agencyId} {$agencyName}</b></span>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;'>الراتب: <b>\${$salary}</b></span>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;'>الخصم: <b>\${$cut}</b></span>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;color:{$statusColor};'>الصافي: <b>{$statusText}</b></span>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;'>رصيد الماس: <b>{$di}</b></span>
                </div>";
            } else {
                $sal = DB::selectOne("SELECT SUM(sallary) as s, SUM(cut_amount) as c FROM agency_sallaries WHERE agency_id=? AND month=? AND year=?", [$eId, $this->targetMonth, $this->targetYear]);
                $agency = DB::table('agencies')->where('id', $eId)->first(['coins']);
                $salary = round($sal->s ?? 0, 2);
                $cut = round($sal->c ?? 0, 2);
                $net = round($salary - $cut, 2);
                $coins = $agency->coins ?? 0;

                $statusColor = $net >= 0 ? '#4ade80' : '#f87171';
                $statusText = $net > 0 ? "هتاخد \${$net}" : ($net == 0 ? 'لا لها ولا عليها' : "عليها \$" . abs($net));

                $salaryInfo = "<div style='display:flex;gap:16px;flex-wrap:wrap;margin:12px 0;'>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;'>الراتب: <b>\${$salary}</b></span>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;'>الخصم: <b>\${$cut}</b></span>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;color:{$statusColor};'>الصافي: <b>{$statusText}</b></span>
                    <span style='background:#1a2332;padding:6px 14px;border-radius:6px;font-size:13px;'>كوينز الوكالة: <b>" . number_format($coins) . "</b></span>
                </div>";
            }

            // Build "as debtor" table
            $debtorTableRows = '';
            $totalDebtorCoins = 0;
            $totalDebtorUsd = 0;
            foreach ($entity['as_debtor'] as $d) {
                $targetName = htmlspecialchars($d['target_name']);
                $targetLabel = ($d['target_type'] === 'agency' || $d['target_type'] === 'وكالة') ? 'وكالة' : 'يوزر';
                $debtorTableRows .= "<tr>
                    <td>{$targetLabel} #{$d['target_id']} - {$targetName}</td>
                    <td>" . number_format($d['amount_coins']) . "</td>
                    <td>\${$d['amount_usd']}</td>
                    <td>{$d['action']}</td>
                </tr>\n";
                $totalDebtorCoins += $d['amount_coins'];
                $totalDebtorUsd += $d['amount_usd'];
            }
            $totalDebtorUsd = round($totalDebtorUsd, 2);
            $debtorTableRows .= "<tr style='background:#162032;font-weight:700;'>
                <td>الإجمالي</td>
                <td>" . number_format($totalDebtorCoins) . "</td>
                <td>\${$totalDebtorUsd}</td>
                <td></td>
            </tr>";

            // Build "as target" table (only entries where THIS entity was deducted from by OTHER debtors)
            $targetTableRows = '';
            $totalTargetCoins = 0;
            $totalTargetUsd = 0;
            $filteredTargets = [];
            foreach ($entity['as_target'] as $d) {
                // Only show if the debtor is someone else
                $sameEntity = ($d['debtor_type'] === $entity['type'] && $d['debtor_id'] === $entity['id']);
                if ($sameEntity) continue;
                $filteredTargets[] = $d;
            }

            $targetSection = '';
            if (!empty($filteredTargets)) {
                foreach ($filteredTargets as $d) {
                    $debtorName = htmlspecialchars($d['debtor_name']);
                    $debtorLabel = ($d['debtor_type'] === 'agency' || $d['debtor_type'] === 'وكالة') ? 'وكالة' : 'يوزر';
                    $targetTableRows .= "<tr>
                        <td>{$debtorLabel} #{$d['debtor_id']} - {$debtorName}</td>
                        <td>" . number_format($d['amount_coins']) . "</td>
                        <td>\${$d['amount_usd']}</td>
                        <td>{$d['action']}</td>
                    </tr>\n";
                    $totalTargetCoins += $d['amount_coins'];
                    $totalTargetUsd += $d['amount_usd'];
                }
                $totalTargetUsd = round($totalTargetUsd, 2);
                $targetTableRows .= "<tr style='background:#162032;font-weight:700;'>
                    <td>الإجمالي</td>
                    <td>" . number_format($totalTargetCoins) . "</td>
                    <td>\${$totalTargetUsd}</td>
                    <td></td>
                </tr>";

                $targetSection = "
                <h4 style='color:#f59e0b;margin:16px 0 8px;font-size:15px;'>اتخصم منه عشان ناس تانية مدينة (وسيط في السلسلة)</h4>
                <div style='overflow-x:auto;'>
                <table class='final-table' style='margin-bottom:0;'>
                <thead><tr><th>المدين الأصلي</th><th>كوينز</th><th>دولار</th><th>العملية</th></tr></thead>
                <tbody>{$targetTableRows}</tbody>
                </table>
                </div>";
            }

            $cardId = "profile-{$key}";
            $html .= "
            <div style='background:#1e293b;border-radius:12px;margin-bottom:16px;border:1px solid #334155;overflow:hidden;'>
                <div style='padding:16px 20px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;' onclick=\"var t=document.getElementById('{$cardId}');t.style.display=t.style.display==='none'?'block':'none';\">
                    <div style='display:flex;align-items:center;gap:10px;'>
                        <span style='font-size:18px;color:#64748b;'>&#9654;</span>
                        <span style='background:#475569;padding:3px 8px;border-radius:4px;font-size:12px;font-family:monospace;'>{$entityLabel} #{$eId}</span>
                        <span style='font-weight:700;font-size:16px;'>{$eName}</span>
                    </div>
                    <div style='display:flex;gap:12px;font-size:13px;'>
                        <span style='color:#4ade80;'>استرداد: \${$totalDebtorUsd}</span>
                        " . ($totalTargetCoins > 0 ? "<span style='color:#f59e0b;'>خصم كوسيط: \${$totalTargetUsd}</span>" : '') . "
                    </div>
                </div>
                <div id='{$cardId}' style='display:none;padding:0 20px 20px;'>
                    {$salaryInfo}
                    <h4 style='color:#38bdf8;margin:12px 0 8px;font-size:15px;'>كان مدين - النظام استرد منه:</h4>
                    <div style='overflow-x:auto;'>
                    <table class='final-table' style='margin-bottom:0;'>
                    <thead><tr><th>اتخصم من مين</th><th>كوينز</th><th>دولار</th><th>العملية</th></tr></thead>
                    <tbody>{$debtorTableRows}</tbody>
                    </table>
                    </div>
                    {$targetSection}
                </div>
            </div>\n";
        }

        return $html;
    }
}
