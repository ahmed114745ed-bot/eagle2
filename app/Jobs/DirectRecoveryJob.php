<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DirectRecoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 1;

    protected $bugDate = '2026-03-01';
    protected $endDate = '2026-04-01';
    protected $targetMonth = 3;
    protected $targetYear = 2026;
    protected $reportFile = 'direct_recovery_report.txt';

    protected $affectedUserIds = [];
    protected $traces = [];
    protected $visitedUsers = [];

    public function handle()
    {
        ini_set('memory_limit', '1G');

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }
        DB::disableQueryLog();

        $zonesCoins = (int) (DB::table('settings')->where('key', 'zones_coins')->value('value') ?? 30000);

        // Get first debtor
        $debtor = DB::selectOne("
            SELECT user_id,
                   SUM(sallary) as total_earned,
                   SUM(cut_amount) as total_cut,
                   SUM(sallary) - SUM(cut_amount) as total_debt
            FROM user_sallaries
            WHERE month = ? AND year = ?
            GROUP BY user_id
            HAVING total_debt < 0
            ORDER BY total_debt ASC
            LIMIT 1
        ", [$this->targetMonth, $this->targetYear]);

        if (!$debtor) {
            $this->appendReport("\n=== ALL DONE === " . now()->toDateTimeString() . "\n");
            Log::info("DirectRecovery: All debtors processed.");
            return;
        }

        $debtUsd = abs($debtor->total_debt);
        $debtCoins = (int) ($debtUsd * $zonesCoins);
        $remaining = $debtCoins;
        $user = DB::table('users')->where('id', $debtor->user_id)->first();
        $userName = $user->name ?? 'N/A';

        DB::beginTransaction();

        try {
            // Get all charges by this debtor
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
                        $this->traces[] = "Agency #{$agency->id}: -{$canDeduct}, charge #{$charge->id} deleted";
                    }
                } else {
                    // User charge: A (debtor) charged B
                    $userB = DB::table('users')->where('id', $charge->user_id)->first();
                    if (!$userB) continue;

                    $chargeAmount = min($remaining, (int) $charge->amount);

                    // Step 1: Delete the charge record (A->B)
                    DB::table('charges')->where('id', $charge->id)->delete();
                    $this->traces[] = "Charge #{$charge->id} deleted (Debtor -> User #{$userB->id})";

                    // Step 2: Check if B has enough di
                    $canDeduct = min($chargeAmount, (int) $userB->di);
                    if ($canDeduct > 0) {
                        DB::statement("UPDATE users SET di = CAST(di AS SIGNED) - ? WHERE id = ?", [$canDeduct, $userB->id]);
                        $remaining -= $canDeduct;
                        $chargeAmount -= $canDeduct;
                        $this->traces[] = "User #{$userB->id} di: -{$canDeduct}";
                    }

                    // Step 3: If B doesn't have enough di, trace B's gifts recursively
                    if ($chargeAmount > 0) {
                        $recovered = $this->traceGifts($userB->id, $chargeAmount, 1);
                        $remaining -= $recovered;
                    }
                }
            }

            // Adjust cut_amount
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

            // Mark affected users for salary recalc (include debtor)
            $this->affectedUserIds[] = $debtor->user_id;
            $affectedUserIds = array_unique($this->affectedUserIds);
            if (!empty($affectedUserIds)) {
                foreach (array_chunk($affectedUserIds, 500) as $chunk) {
                    DB::table('users')
                        ->whereIn('id', $chunk)
                        ->update(['salary_is_updated' => 0]);
                }
            }

            // Mark debtor as processed (zero out salary so loop moves on)
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

            // Report
            $line = "User #{$debtor->user_id} ({$userName}): debt=\${$debtUsd}";
            if ($recoveredCoins > 0) {
                $line .= ", recovered=\$" . round($recoveredUsd, 2);
            }
            if ($remaining > 0) {
                $line .= ", STILL_NEGATIVE=\$" . round($remaining / $zonesCoins, 2);
            } else {
                $line .= ", FULLY_RECOVERED";
            }
            $line .= "\n";
            foreach ($this->traces as $t) {
                $line .= "  -> {$t}\n";
            }
            $this->appendReport($line);

            Log::info("DirectRecovery: Processed user {$debtor->user_id}. Recovered: {$recoveredCoins} coins.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->appendReport("ERROR User #{$debtor->user_id}: {$e->getMessage()}\n");
            Log::error("DirectRecovery: Failed for user {$debtor->user_id}: " . $e->getMessage());
        }

        // Dispatch next debtor
        self::dispatch()->delay(now()->addSeconds(2));
    }

    /**
     * Recursively trace gifts from a sender and recover coins.
     * Returns the total amount recovered.
     */
    private function traceGifts(int $senderId, int $amount, int $depth): int
    {
        if ($amount <= 0 || $depth > 10) return 0;

        // Prevent infinite loops
        if (in_array($senderId, $this->visitedUsers)) return 0;
        $this->visitedUsers[] = $senderId;

        $indent = str_repeat('  ', $depth);
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

            // Delete gift_logs (sender -> receiver) and collect room/family data
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

            // Reverse total_diamond_received for receiver
            DB::statement(
                "UPDATE users SET total_diamond_received = GREATEST(0, CAST(total_diamond_received AS SIGNED) - ?) WHERE id = ?",
                [$deletedAmount, $receiver->id]
            );

            // Reverse exchange_diamonds for receiver (only non-agency users)
            DB::statement(
                "UPDATE users SET exchange_diamonds = GREATEST(0, CAST(exchange_diamonds AS SIGNED) - ?) WHERE id = ? AND agency_id = 0",
                [$deletedAmount, $receiver->id]
            );

            // Reverse monthly_diamond_received for receiver
            DB::statement(
                "UPDATE monthly_diamond_receives SET monthly_diamond_received = GREATEST(0, CAST(monthly_diamond_received AS SIGNED) - ?) WHERE user_id = ? AND month = ? AND year = ?",
                [$deletedAmount, $receiver->id, $this->targetMonth, $this->targetYear]
            );

            // Reverse monthly_diamond_send for sender
            DB::statement(
                "UPDATE users SET monthly_diamond_send = GREATEST(0, CAST(monthly_diamond_send AS SIGNED) - ?) WHERE id = ?",
                [$deletedAmount, $senderId]
            );

            // Reverse room session & room_top_users
            foreach ($roomAmounts as $roomId => $roomAmount) {
                DB::statement(
                    "UPDATE rooms SET session = GREATEST(0, CAST(session AS SIGNED) - ?) WHERE id = ?",
                    [$roomAmount, $roomId]
                );
                DB::statement(
                    "UPDATE room_top_users SET coins = GREATEST(0, CAST(coins AS SIGNED) - ?) WHERE room_id = ? AND user_id = ?",
                    [$roomAmount, $roomId, $senderId]
                );
            }

            // Reverse family total_diamond
            foreach ($familyAmounts as $familyId => $famAmount) {
                DB::statement(
                    "UPDATE families SET total_diamond = GREATEST(0, CAST(total_diamond AS SIGNED) - ?) WHERE id = ?",
                    [$famAmount, $familyId]
                );
            }

            $this->affectedUserIds[] = $receiver->id;
            $this->affectedUserIds[] = $senderId;

            $this->traces[] = "{$indent}Gift: #{$senderId} -> #{$receiver->id}: -{$deletedAmount}";

            $amount -= $deletedAmount;
            $totalRecovered += $deletedAmount;

            // Recurse: trace receiver's gifts if we still need more
            if ($amount > 0) {
                $subRecovered = $this->traceGifts($receiver->id, $amount, $depth + 1);
                $amount -= $subRecovered;
                $totalRecovered += $subRecovered;
            }
        }

        return $totalRecovered;
    }

    private function appendReport(string $content): void
    {
        $path = public_path($this->reportFile);
        if (!file_exists($path)) {
            $header = "=== Direct Recovery Report ===\nStarted: " . now()->toDateTimeString() . "\n\n";
            file_put_contents($path, $header);
        }
        file_put_contents($path, $content, FILE_APPEND);
    }
}
