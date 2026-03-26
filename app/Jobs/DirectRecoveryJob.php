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

    public $timeout = 300;
    public $tries = 1;

    protected $bugDate = '2026-03-01';
    protected $endDate = '2026-04-01';
    protected $targetMonth = 3;
    protected $targetYear = 2026;
    protected $reportFile = 'direct_recovery_report.txt';

    public function handle()
    {
        ini_set('memory_limit', '1G');

        // Disable Telescope to save memory
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        // Disable query log
        DB::disableQueryLog();

        $zonesCoins = (int) (DB::table('settings')->where('key', 'zones_coins')->value('value') ?? 30000);

        // Get first debtor only
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

        // No more debtors — write final report
        if (!$debtor) {
            $this->appendReport("=== ALL DONE === " . now()->toDateTimeString() . "\n");
            Log::info("DirectRecovery: All debtors processed. Report at public/{$this->reportFile}");
            return;
        }

        $debtUsd = abs($debtor->total_debt);
        $debtCoins = (int) ($debtUsd * $zonesCoins);
        $remaining = $debtCoins;

        $user = DB::table('users')->where('id', $debtor->user_id)->first();
        $userName = $user->name ?? 'N/A';
        $affectedUserIds = [];
        $traces = [];

        DB::beginTransaction();

        try {
            // ============================================
            // Scenario A: All charges (agency + user)
            // ============================================
            $allCharges = DB::table('charges')
                ->where('charger_id', $debtor->user_id)
                ->where('charger_type', 'user')
                ->whereIn('user_type', ['agency', 'user'])
                ->where('created_at', '>=', $this->bugDate)
                ->orderByDesc('created_at')
                ->cursor();

            foreach ($allCharges as $charge) {
                if ($remaining <= 0) break;

                if ($charge->user_type === 'agency') {
                    $agency = DB::table('agencies')->where('id', $charge->user_id)->first();
                    if (!$agency) continue;

                    $canDeduct = min(min($remaining, (int) $charge->amount), (int) $agency->coins);
                    if ($canDeduct > 0) {
                        DB::statement("UPDATE agencies SET coins = CAST(coins AS SIGNED) - ? WHERE id = ?", [$canDeduct, $agency->id]);
                        $remaining -= $canDeduct;
                        $traces[] = "Agency #{$agency->id}: -{$canDeduct}";
                    }
                } else {
                    $recipient = DB::table('users')->where('id', $charge->user_id)->first();
                    if (!$recipient) continue;

                    $deductAmount = min($remaining, (int) $charge->amount);
                    $canDeduct = min($deductAmount, (int) $recipient->di);

                    if ($canDeduct > 0) {
                        DB::statement("UPDATE users SET di = CAST(di AS SIGNED) - ? WHERE id = ?", [$canDeduct, $recipient->id]);
                        $remaining -= $canDeduct;
                        $deductAmount -= $canDeduct;
                        $traces[] = "User #{$recipient->id} di: -{$canDeduct}";
                    }

                    if ($deductAmount > 0) {
                        $remaining = $this->traceGiftChain($recipient->id, $remaining, $affectedUserIds, $traces);
                    }
                }
            }

            // ============================================
            // Scenario B: Gifts sent by debtor
            // ============================================
            if ($remaining > 0) {
                $remaining = $this->traceGiftChain($debtor->user_id, $remaining, $affectedUserIds, $traces);
            }

            // ============================================
            // Final: Adjust cut_amount + flag affected
            // ============================================
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

                $affectedUserIds = array_unique($affectedUserIds);
                if (!empty($affectedUserIds)) {
                    DB::table('users')
                        ->whereIn('id', $affectedUserIds)
                        ->update(['salary_is_updated' => 0]);
                }
            }

            // Mark debtor as processed (set cut = earned so debt = 0)
            // This ensures the loop moves to the next debtor
            if ($remaining > 0) {
                $salaryRecord = $salaryRecord ?? DB::table('user_sallaries')
                    ->where('user_id', $debtor->user_id)
                    ->where('month', $this->targetMonth)
                    ->where('year', $this->targetYear)
                    ->orderByDesc('cut_amount')
                    ->first();

                if ($salaryRecord) {
                    // Set cut = sallary so debt becomes exactly 0
                    DB::table('user_sallaries')
                        ->where('id', $salaryRecord->id)
                        ->update(['cut_amount' => $salaryRecord->sallary]);
                }
            }

            DB::commit();

            // Append to report
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
            foreach ($traces as $t) {
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

    private function traceGiftChain(int $senderId, int $remaining, array &$affectedUserIds, array &$traces, int $depth = 0, array $visitedUsers = []): int
    {
        if ($depth > 2 || $remaining <= 0) return $remaining;
        if (in_array($senderId, $visitedUsers)) return $remaining;
        $visitedUsers[] = $senderId;

        $gifts = DB::select("
            SELECT receiver_id, SUM(giftPrice) as total_sent
            FROM gift_logs
            WHERE sender_id = ? AND created_at >= ? AND created_at < ?
            GROUP BY receiver_id
            ORDER BY total_sent DESC
            LIMIT 20
        ", [$senderId, $this->bugDate, $this->endDate]);

        foreach ($gifts as $gift) {
            if ($remaining <= 0) break;
            if ($gift->receiver_id == $senderId || in_array($gift->receiver_id, $visitedUsers)) continue;

            $receiver = DB::table('users')->where('id', $gift->receiver_id)->first();
            if (!$receiver) continue;

            $deductAmount = min($remaining, (int) $gift->total_sent);
            $canDeduct = min($deductAmount, (int) $receiver->di);

            if ($canDeduct > 0) {
                DB::statement("UPDATE users SET di = CAST(di AS SIGNED) - ? WHERE id = ?", [$canDeduct, $receiver->id]);
                DB::statement("UPDATE users SET total_diamond_received = GREATEST(0, CAST(total_diamond_received AS SIGNED) - ?) WHERE id = ?", [$canDeduct, $receiver->id]);
                DB::statement(
                    "UPDATE monthly_diamond_receives SET monthly_diamond_received = GREATEST(0, CAST(monthly_diamond_received AS SIGNED) - ?) WHERE user_id = ? AND month = ? AND year = ?",
                    [$canDeduct, $receiver->id, $this->targetMonth, $this->targetYear]
                );

                // Delete gift_logs
                $remainingToDelete = $canDeduct;
                $idsToDelete = [];
                $giftLogRows = DB::table('gift_logs')
                    ->where('sender_id', $senderId)
                    ->where('receiver_id', $receiver->id)
                    ->where('created_at', '>=', $this->bugDate)
                    ->where('created_at', '<', $this->endDate)
                    ->orderBy('giftPrice')
                    ->select('id', 'giftPrice')
                    ->cursor();

                foreach ($giftLogRows as $row) {
                    if ($remainingToDelete <= 0) break;
                    $idsToDelete[] = $row->id;
                    $remainingToDelete -= (int) $row->giftPrice;
                }
                if (!empty($idsToDelete)) {
                    DB::table('gift_logs')->whereIn('id', $idsToDelete)->delete();
                }

                $affectedUserIds[] = $receiver->id;
                $remaining -= $canDeduct;
                $deductAmount -= $canDeduct;

                $depthLabel = str_repeat('  ', $depth);
                $traces[] = "{$depthLabel}Gift: #{$senderId} -> #{$receiver->id}: -{$canDeduct}";
            }

            if ($deductAmount > 0 && $depth < 3) {
                $remaining = $this->traceGiftChain($receiver->id, $remaining, $affectedUserIds, $traces, $depth + 1, $visitedUsers);
            }
        }

        return $remaining;
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
