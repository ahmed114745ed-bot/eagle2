<?php

namespace Modules\UsersWallet\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Agency;
use Modules\UsersWallet\Helpers\WalletHelper;

class RecalculateWalletsSeeder extends Seeder
{
    public function run()
    {
        // Truncate wallets and logs
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('wallet_logs')->truncate();
        DB::table('users_wallets')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Recalculate balances per UserSallary (preserve per-target wallet logs and distributions)
        \App\Models\UserSallary::query()
            ->orderBy('id')
            ->chunk(500, function ($salaries) {
                foreach ($salaries as $s) {
                    try {
                        $newData = [
                            'sallary' => (float) $s->sallary,
                            'agency_sallary' => (float) $s->agency_sallary,
                            'dB' => (float) $s->dB,
                        ];

                        // Apply salary for this target (acts like UpdateUserWalletBalances job)
                        WalletHelper::addAllBalancesByDiffs($s->user_id, $newData, ['sallary' => 0, 'agency_sallary' => 0, 'dB' => 0], $s->user_agency_id ?? null, 'sallary_update', $s->target_id);

                        // Apply cut_amount for this target directly to user's wallet and log it
                        if ((float) $s->cut_amount > 0) {
                            $walletModel = \Modules\UsersWallet\Entities\UserWallet::firstOrCreate(['user_id' => $s->user_id]);
                            $before = wallet_available_by_wallet($walletModel);
                            $walletModel->cut_amount += (float) $s->cut_amount;
                            $walletModel->save();
                            $after = wallet_available_by_wallet($walletModel);

                            \Modules\UsersWallet\Entities\WalletLog::create([
                                'wallet_id' => $walletModel->id,
                                'user_id' => $s->user_id,
                                'amount' => -(float) $s->cut_amount,
                                'operation' => 'subtract',
                                'type' => 'user',
                                'before_amount' => $before,
                                'after_amount' => $after,
                                'related_id' => $s->target_id,
                            ]);
                        }

                    } catch (\Throwable $e) {
                        Log::error('RecalculateWalletsSeeder failed for salary '.$s->id, ['err' => $e->getMessage()]);
                    }
                }
            });

        // Set pending_amount for users from pending withdrawals
        DB::table('user_withdrawals')
            ->select('user_id', DB::raw('COALESCE(SUM(amount),0) as total_pending'))
            ->where('status', 'pending')
            ->groupBy('user_id')
            ->orderBy('user_id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $r) {
                    try {
                        DB::table('users_wallets')->updateOrInsert(
                            ['user_id' => $r->user_id],
                            ['pending_amount' => (float) $r->total_pending, 'updated_at' => now(), 'created_at' => now()]
                        );
                    } catch (\Throwable $e) {
                        Log::error('RecalculateWalletsSeeder failed setting pending for user '.$r->user_id, ['err' => $e->getMessage()]);
                    }
                }
            });

        // Apply agency_sallaries cut_amounts to agency owners' wallets (record as subtract logs)
        DB::table('agency_sallaries')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $a) {
                try {
                    if ((float) $a->cut_amount > 0) {
                        $agency = Agency::find($a->agency_id);
                        if (! $agency || ! $agency->app_owner_id) {
                            continue;
                        }

                        $ownerId = $agency->app_owner_id;
                        $walletModel = \Modules\UsersWallet\Entities\UserWallet::firstOrCreate(['user_id' => $ownerId]);
                        $before = wallet_available_by_wallet($walletModel);
                        $walletModel->cut_amount += (float) $a->cut_amount;
                        $walletModel->save();

                        \Modules\UsersWallet\Entities\WalletLog::create([
                            'wallet_id' => $walletModel->id,
                            'user_id' => $ownerId,
                            'amount' => - (float) $a->cut_amount,
                            'operation' => 'subtract',
                            'type' => 'agency_owner',
                            'before_amount' => $before,
                            'after_amount' => wallet_available_by_wallet($walletModel),
                            'related_id' => $a->id,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('RecalculateWalletsSeeder failed applying agency cut for agency_sallary '.$a->id, ['err' => $e->getMessage()]);
                }
            }
        });

        // (No separate agency aggregation here) agency owner and bd shares are handled per UserSallary above via WalletHelper

        // BD aggregation: bd_salaries + bd_agency_host_sallaries — apply with WalletLog entries
        // BD aggregation: map bd records to their app user and credit the correct user wallets
        \App\Models\BdSalary::query()->orderBy('id')->chunk(200, function ($bdRows) {
            foreach ($bdRows as $b) {
                try {
                    // bd_id refers to admin_users.id (Bd). We need the related app user id (app_id) to credit the correct wallet
                    $bd = \App\Models\Bd::find($b->bd_id);
                    if (! $bd || ! $bd->app_id) {
                        Log::warning('RecalculateWalletsSeeder: bd record missing or not linked to an app user for bd_salary '.$b->id, ['bd_id' => $b->bd_id]);
                        continue;
                    }

                    $userId = $bd->app_id;

                    // bd_agency_host_sallaries amounts are already applied per-target via UserSallary (dB) processing.
                    // Here we should only add the BdSalary main amount to avoid double-counting host sums.
                    $addBalance = (float) $b->salary;
                    $addCut = (float) $b->cut_amount;

                    $walletModel = \Modules\UsersWallet\Entities\UserWallet::firstOrCreate(['user_id' => $userId]);
                    $before = wallet_available_by_wallet($walletModel);

                    // Idempotent BD salary application: consider only logs that actually changed balance
                    // (ignore log-only entries created by per-target dB processing where before_amount == after_amount)
                    $existingBdNet = (float) DB::table('wallet_logs')
                        ->where('user_id', $userId)
                        ->where('related_id', $b->id)
                        ->where('type', 'bd')
                        ->whereColumn('after_amount','<>','before_amount')
                        ->sum('amount');

                    $diff = round($addBalance - $existingBdNet, 8);
                    if ($diff != 0) {
                        $before = wallet_available_by_wallet($walletModel);
                        if ($diff > 0) {
                            $walletModel->balance += $diff;
                            $walletModel->save();
                            \Modules\UsersWallet\Entities\WalletLog::create([
                                'wallet_id' => $walletModel->id,
                                'user_id' => $userId,
                                'amount' => $diff,
                                'operation' => 'add',
                                'type' => 'bd',
                                'before_amount' => $before,
                                'after_amount' => wallet_available_by_wallet($walletModel),
                                'related_id' => $b->id,
                            ]);
                        } else {
                            // negative diff -> remove excess (record as subtract)
                            $walletModel->balance += $diff; // diff negative
                            $walletModel->save();
                            \Modules\UsersWallet\Entities\WalletLog::create([
                                'wallet_id' => $walletModel->id,
                                'user_id' => $userId,
                                'amount' => $diff,
                                'operation' => 'subtract',
                                'type' => 'bd',
                                'before_amount' => $before,
                                'after_amount' => wallet_available_by_wallet($walletModel),
                                'related_id' => $b->id,
                            ]);
                        }
                    }

                    // Apply cut: sum existing subtract logs of type 'bd' (these are negative amounts);
                    // treat cuts as wallet cut_amount and log them with type 'bd' and operation 'subtract'
                    $existingCutSum = (float) DB::table('wallet_logs')
                        ->where('user_id', $userId)
                        ->where('related_id', $b->id)
                        ->where('type', 'bd')
                        ->where('operation', 'subtract')
                        ->sum('amount'); // negative values expected

                    $existingCutAbs = abs($existingCutSum);
                    $cutDiff = round($addCut - $existingCutAbs, 8);

                    if ($cutDiff != 0) {
                        $beforeCut = wallet_available_by_wallet($walletModel);
                        // if cutDiff > 0 we need to apply more cut (subtract), if < 0 we need to revert some cut (add)
                        if ($cutDiff > 0) {
                            $walletModel->cut_amount += $cutDiff;
                            $walletModel->save();

                            \Modules\UsersWallet\Entities\WalletLog::create([
                                'wallet_id' => $walletModel->id,
                                'user_id' => $userId,
                                'amount' => -$cutDiff,
                                'operation' => 'subtract',
                                'type' => 'bd',
                                'before_amount' => $beforeCut,
                                'after_amount' => wallet_available_by_wallet($walletModel),
                                'related_id' => $b->id,
                            ]);
                        } else {
                            // revert excess cut
                            $revert = abs($cutDiff);
                            $walletModel->cut_amount = max(0, $walletModel->cut_amount - $revert);
                            $walletModel->save();

                            \Modules\UsersWallet\Entities\WalletLog::create([
                                'wallet_id' => $walletModel->id,
                                'user_id' => $userId,
                                'amount' => $revert,
                                'operation' => 'add',
                                'type' => 'bd',
                                'before_amount' => $beforeCut,
                                'after_amount' => wallet_available_by_wallet($walletModel),
                                'related_id' => $b->id,
                            ]);
                        }
                    }

                } catch (\Throwable $e) {
                    Log::error('RecalculateWalletsSeeder failed for bd '.$b->id, ['err' => $e->getMessage()]);
                }
            }
        });

        // Verification: compare wallets to expected sums and fail if mismatch
        $errors = [];
        $stop = false;
        DB::table('users')->select('id')->orderBy('id')->chunk(500, function ($rows) use (&$errors, &$stop) {
            foreach ($rows as $r) {
                $uid = $r->id;

                // user's own salary and cut
                $ownSalary = (float) DB::table('user_sallaries')->where('user_id', $uid)->sum('sallary');
                $ownCut = (float) DB::table('user_sallaries')->where('user_id', $uid)->sum('cut_amount');

                // agency sums where user is agency owner
                $agencySalarySum = (float) DB::table('agency_sallaries as a')
                    ->join('agencies as g', 'a.agency_id', '=', 'g.id')
                    ->where('g.app_owner_id', $uid)
                    ->sum('a.sallary');

                $agencyCutSum = (float) DB::table('agency_sallaries as a')
                    ->join('agencies as g', 'a.agency_id', '=', 'g.id')
                    ->where('g.app_owner_id', $uid)
                    ->sum('a.cut_amount');

                // BD sums for this user: find bd records (admin_users) that link to this app user and aggregate their salaries/cuts.
                // NOTE: bd_agency_host_sallaries (host amounts) are already applied per-target when processing UserSallary (dB)
                // and are recorded as WalletLog entries. To avoid double-counting we treat host amounts as separate logs
                // and exclude them from the aggregated expectedBalance here.
                $bdIds = DB::table('admin_users')->where('app_id', $uid)->pluck('id')->toArray();
                if (!empty($bdIds)) {
                    $bdSalarySum = (float) DB::table('bd_salaries')->whereIn('bd_id', $bdIds)->sum('salary');
                    $bdCutSum = (float) DB::table('bd_salaries')->whereIn('bd_id', $bdIds)->sum('cut_amount');
                    $bdHostSum = 0.0; // excluded to avoid double-counting (host amounts handled via per-target processing)
                } else {
                    $bdSalarySum = 0.0;
                    $bdCutSum = 0.0;
                    $bdHostSum = 0.0;
                }

                $expectedBalance = $ownSalary + $agencySalarySum + $bdSalarySum + $bdHostSum;
                $expectedCut = $ownCut + $agencyCutSum + $bdCutSum;
                $expectedPending = (float) DB::table('user_withdrawals')->where('user_id', $uid)->where('status', 'pending')->sum('amount');

                $wallet = DB::table('users_wallets')->where('user_id', $uid)->first();
                $actualBalance = (float) ($wallet->balance ?? 0);
                $actualCut = (float) ($wallet->cut_amount ?? 0);
                $actualPending = (float) ($wallet->pending_amount ?? 0);

                if (abs($expectedBalance - $actualBalance) > 0.01 || abs($expectedCut - $actualCut) > 0.01 || abs($expectedPending - $actualPending) > 0.01) {
                    // Add detailed debug info for first mismatches to help root-cause
                    try {
                        $logs = DB::table('wallet_logs')->where('user_id', $uid)->orderBy('id')->get();
                        $logSums = DB::table('wallet_logs')->where('user_id', $uid)->selectRaw('type, SUM(amount) as total')->groupBy('type')->get();

                        $userSallariesRows = DB::table('user_sallaries')->where('user_id', $uid)->get();
                        $agencySallariesRows = DB::table('agency_sallaries as a')
                            ->join('agencies as g', 'a.agency_id', '=', 'g.id')
                            ->where('g.app_owner_id', $uid)
                            ->select('a.*')
                            ->get();

                        // BD records that map to this user
                        $bdIds = DB::table('admin_users')->where('app_id', $uid)->pluck('id')->toArray();

                        $bdSallariesRows = !empty($bdIds) ? DB::table('bd_salaries')->whereIn('bd_id', $bdIds)->get() : collect();
                        $bdHostRows = !empty($bdIds) ? DB::table('bd_agency_host_sallaries')->whereIn('bd_id', $bdIds)->get() : collect();

                        Log::error('RecalculateWalletsSeeder mismatch details', [
                            'user' => $uid,
                            'expected' => [$expectedBalance, $expectedCut, $expectedPending],
                            'actual' => [$actualBalance, $actualCut, $actualPending],
                            'wallet_logs' => $logs,
                            'wallet_log_sums' => $logSums,
                            'user_sallaries' => $userSallariesRows,
                            'agency_sallaries' => $agencySallariesRows,
                            'bd_ids' => $bdIds,
                            'bd_sallaries' => $bdSallariesRows,
                            'bd_hosts' => $bdHostRows,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('RecalculateWalletsSeeder failed collecting debug for user '.$uid, ['err' => $e->getMessage()]);
                    }

                    $errors[] = ['user' => $uid, 'expected' => [$expectedBalance, $expectedCut, $expectedPending], 'actual' => [$actualBalance, $actualCut, $actualPending]];
                    if (count($errors) >= 50) { $stop = true; break; } // stop early if many
                }
            }

            if ($stop) {
                return false; // stop chunking early
            }
        });

        if (count($errors) > 0) {
            Log::error('RecalculateWalletsSeeder verification failed. First mismatches: '.json_encode(array_slice($errors,0,10)));
            throw new \Exception('RecalculateWalletsSeeder verification failed: wallets do not match sums.');
        }

        Log::info('RecalculateWalletsSeeder completed successfully. Wallets rebuilt and verified.');
    }
}
