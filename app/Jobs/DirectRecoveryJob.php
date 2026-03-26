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
    protected $reportFile = 'direct_recovery_report.html';

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
        DB::statement("SET SESSION innodb_lock_wait_timeout = 120");

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
            $this->finishReport();
            Log::info("DirectRecovery: All debtors processed.");
            return;
        }

        $debtUsd = abs($debtor->total_debt);
        $debtCoins = (int) ($debtUsd * $zonesCoins);
        $remaining = $debtCoins;
        $user = DB::table('users')->where('id', $debtor->user_id)->first();
        $userName = $user->name ?? 'N/A';

        $maxRetries = 3;
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {

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
                        $this->traces[] = ['depth' => 0, 'type' => 'agency', 'text' => "Agency #{$agency->id}: -{$canDeduct} coins, charge #{$charge->id} deleted"];
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
                    }

                    if ($chargeAmount > 0) {
                        $recovered = $this->traceGifts($userB->id, $chargeAmount, 1);
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
                    DB::table('users')
                        ->whereIn('id', $chunk)
                        ->update(['salary_is_updated' => 0]);
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

            $status = $remaining > 0 ? 'partial' : 'recovered';
            $this->appendUserRow($debtor->user_id, $userName, $debtUsd, $recoveredUsd, $remaining / $zonesCoins, $status);

            Log::info("DirectRecovery: Processed user {$debtor->user_id}. Recovered: {$recoveredCoins} coins.");

        } catch (\Exception $e) {
            DB::rollBack();

            // Retry on deadlock
            if ($attempt < $maxRetries && str_contains($e->getMessage(), 'Deadlock')) {
                Log::warning("DirectRecovery: Deadlock for user {$debtor->user_id}, retry {$attempt}/{$maxRetries}");
                $this->traces = [];
                $this->affectedUserIds = [];
                $this->visitedUsers = [];
                sleep(2);
                continue;
            }

            $this->appendUserRow($debtor->user_id, $userName, $debtUsd, 0, $debtUsd, 'error', $e->getMessage());
            Log::error("DirectRecovery: Failed for user {$debtor->user_id}: " . $e->getMessage());

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
            } catch (\Exception $ex) {
                // ignore
            }
        }

        break; // Success or non-deadlock error, exit retry loop
        }

        self::dispatch()->delay(now()->addSeconds(2));
    }

    private function traceGifts(int $senderId, int $amount, int $depth): int
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

            $amount -= $deletedAmount;
            $totalRecovered += $deletedAmount;

            if ($amount > 0) {
                $subRecovered = $this->traceGifts($receiver->id, $amount, $depth + 1);
                $amount -= $subRecovered;
                $totalRecovered += $subRecovered;
            }
        }

        return $totalRecovered;
    }

    private function initReport(): void
    {
        $path = public_path($this->reportFile);
        if (!file_exists($path)) {
            $html = <<<'HTML'
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Direct Recovery Report</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #0f172a; color: #e2e8f0; padding: 20px; direction: ltr; }
.header { text-align: center; padding: 30px; background: linear-gradient(135deg, #1e293b, #334155); border-radius: 16px; margin-bottom: 30px; border: 1px solid #475569; }
.header h1 { font-size: 28px; color: #38bdf8; margin-bottom: 8px; }
.header .date { color: #94a3b8; font-size: 14px; }
.user-card { background: #1e293b; border-radius: 12px; margin-bottom: 16px; border: 1px solid #334155; overflow: hidden; }
.user-header { padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: background 0.2s; }
.user-header:hover { background: #334155; }
.user-info { display: flex; align-items: center; gap: 12px; }
.user-id { background: #475569; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-family: monospace; }
.user-name { font-weight: 600; font-size: 15px; }
.badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-recovered { background: #065f46; color: #6ee7b7; }
.badge-partial { background: #78350f; color: #fcd34d; }
.badge-error { background: #7f1d1d; color: #fca5a5; }
.amounts { display: flex; gap: 20px; font-size: 13px; color: #94a3b8; }
.amounts span { display: flex; align-items: center; gap: 4px; }
.amount-label { color: #64748b; }
.traces { padding: 0 20px 16px; display: none; }
.traces.open { display: block; }
.trace { padding: 4px 0; font-size: 13px; font-family: monospace; color: #94a3b8; border-left: 2px solid #334155; margin-left: 8px; padding-left: 12px; }
.trace.depth-0 { border-left-color: #38bdf8; color: #7dd3fc; }
.trace.depth-1 { margin-left: 24px; border-left-color: #a78bfa; color: #c4b5fd; }
.trace.depth-2 { margin-left: 40px; border-left-color: #fb923c; color: #fdba74; }
.trace.depth-3 { margin-left: 56px; border-left-color: #4ade80; color: #86efac; }
.trace.depth-4, .trace.depth-5, .trace.depth-6, .trace.depth-7, .trace.depth-8, .trace.depth-9, .trace.depth-10
{ margin-left: 72px; border-left-color: #f472b6; color: #f9a8d4; }
.trace .type-icon { margin-right: 6px; }
.error-msg { color: #fca5a5; font-size: 12px; padding: 8px 20px; background: #450a0a; margin: 0 20px 16px; border-radius: 6px; }
.toggle-arrow { transition: transform 0.2s; font-size: 18px; color: #64748b; }
.toggle-arrow.open { transform: rotate(90deg); }
#summary { display: none; }
.summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px; }
.stat-card { background: #1e293b; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #334155; }
.stat-value { font-size: 36px; font-weight: 700; margin-bottom: 4px; }
.stat-label { font-size: 13px; color: #94a3b8; }
.stat-green .stat-value { color: #4ade80; }
.stat-yellow .stat-value { color: #fbbf24; }
.stat-red .stat-value { color: #f87171; }
.stat-blue .stat-value { color: #38bdf8; }
.summary-table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 12px; overflow: hidden; }
.summary-table th { background: #334155; padding: 12px 16px; text-align: left; font-size: 13px; color: #94a3b8; }
.summary-table td { padding: 10px 16px; border-top: 1px solid #334155; font-size: 14px; }
.summary-table tr:hover td { background: #334155; }
</style>
</head>
<body>
<div class="header">
<h1>Direct Recovery Report</h1>
<div class="date">Started: REPORT_DATE</div>
</div>
<div id="users">
HTML;
            $html = str_replace('REPORT_DATE', now()->toDateTimeString(), $html);
            file_put_contents($path, $html);
        }
    }

    private function appendUserRow(int $userId, string $userName, float $debt, float $recovered, float $stillNegative, string $status, string $errorMsg = ''): void
    {
        $this->initReport();
        $path = public_path($this->reportFile);

        $badgeClass = match($status) {
            'recovered' => 'badge-recovered',
            'partial' => 'badge-partial',
            'error' => 'badge-error',
            default => 'badge-partial',
        };
        $badgeText = match($status) {
            'recovered' => 'FULLY RECOVERED',
            'partial' => 'STILL NEGATIVE',
            'error' => 'ERROR',
            default => $status,
        };

        $userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $cardId = "user-{$userId}-" . time();

        $tracesHtml = '';
        foreach ($this->traces as $t) {
            $depth = $t['depth'];
            $depthClass = "depth-{$depth}";
            $icon = match($t['type']) {
                'agency' => '&#127970;',
                'charge' => '&#128465;',
                'di' => '&#128176;',
                'gift' => '&#127873;',
                default => '&#8226;',
            };
            $tracesHtml .= "<div class=\"trace {$depthClass}\"><span class=\"type-icon\">{$icon}</span>{$t['text']}</div>\n";
        }

        $errorHtml = $errorMsg ? '<div class="error-msg">' . htmlspecialchars(substr($errorMsg, 0, 200)) . '</div>' : '';

        $html = <<<HTML
<div class="user-card">
<div class="user-header" onclick="var t=document.getElementById('{$cardId}');var a=this.querySelector('.toggle-arrow');t.classList.toggle('open');a.classList.toggle('open');">
<div class="user-info">
<span class="toggle-arrow">&#9654;</span>
<span class="user-id">#{$userId}</span>
<span class="user-name">{$userName}</span>
</div>
<div style="display:flex;align-items:center;gap:16px;">
<div class="amounts">
<span><span class="amount-label">Debt:</span> \${$debt}</span>
<span><span class="amount-label">Recovered:</span> \$RECOVERED_VAL</span>
STILL_NEG_SPAN
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

        $html = str_replace('RECOVERED_VAL', round($recovered, 2), $html);
        $stillNegSpan = $stillNegative > 0 ? '<span><span class="amount-label">Remaining:</span> $' . round($stillNegative, 2) . '</span>' : '';
        $html = str_replace('STILL_NEG_SPAN', $stillNegSpan, $html);

        file_put_contents($path, $html, FILE_APPEND);
    }

    private function finishReport(): void
    {
        $path = public_path($this->reportFile);
        if (!file_exists($path)) return;

        // Check if already finalized
        if (str_contains(file_get_contents($path), 'id="summary"')) {
            $content = file_get_contents($path);
            if (str_contains($content, 'display: block')) return;
        }

        // Read all user_sallaries data to build summary
        $allProcessed = DB::select("
            SELECT user_id, SUM(sallary) as total_earned, SUM(cut_amount) as total_cut,
                   SUM(sallary) - SUM(cut_amount) as net
            FROM user_sallaries
            WHERE month = ? AND year = ?
            GROUP BY user_id
            HAVING net <= 0
        ", [$this->targetMonth, $this->targetYear]);

        // We just close the HTML with summary stats parsed from the file itself
        $content = file_get_contents($path);

        // Count stats from written cards
        preg_match_all('/badge-recovered/', $content, $m1);
        preg_match_all('/badge-partial/', $content, $m2);
        preg_match_all('/badge-error/', $content, $m3);
        $fullyRecovered = count($m1[0]);
        $stillNegative = count($m2[0]);
        $errors = count($m3[0]);
        $total = $fullyRecovered + $stillNegative + $errors;

        // Extract dollar amounts
        preg_match_all('/Debt:<\/span>\s*\$([0-9.]+)/', $content, $debts);
        preg_match_all('/Recovered:<\/span>\s*\$([0-9.]+)/', $content, $recs);
        $totalDebt = array_sum(array_map('floatval', $debts[1]));
        $totalRecovered = array_sum(array_map('floatval', $recs[1]));
        $recoveryRate = $totalDebt > 0 ? round(($totalRecovered / $totalDebt) * 100, 1) : 0;

        $summaryHtml = <<<HTML
</div>
<div id="summary" style="display: block; margin-top: 40px;">
<div class="header" style="margin-bottom: 20px;">
<h1>Summary</h1>
<div class="date">Completed: FINISH_DATE</div>
</div>
<div class="summary-grid">
<div class="stat-card stat-blue">
<div class="stat-value">{$total}</div>
<div class="stat-label">Total Processed</div>
</div>
<div class="stat-card stat-green">
<div class="stat-value">{$fullyRecovered}</div>
<div class="stat-label">Fully Recovered</div>
</div>
<div class="stat-card stat-yellow">
<div class="stat-value">{$stillNegative}</div>
<div class="stat-label">Still Negative</div>
</div>
<div class="stat-card stat-red">
<div class="stat-value">{$errors}</div>
<div class="stat-label">Errors</div>
</div>
<div class="stat-card stat-blue">
<div class="stat-value">\$TOTAL_DEBT</div>
<div class="stat-label">Total Debt</div>
</div>
<div class="stat-card stat-green">
<div class="stat-value">\$TOTAL_RECOVERED</div>
<div class="stat-label">Total Recovered</div>
</div>
<div class="stat-card" style="">
<div class="stat-value" style="color: #c084fc;">{$recoveryRate}%</div>
<div class="stat-label">Recovery Rate</div>
</div>
</div>
</div>
<script>
document.querySelectorAll('.user-header').forEach(function(el) {
el.style.cursor = 'pointer';
});
</script>
</body>
</html>
HTML;

        $summaryHtml = str_replace('FINISH_DATE', now()->toDateTimeString(), $summaryHtml);
        $summaryHtml = str_replace('TOTAL_DEBT', round($totalDebt, 2), $summaryHtml);
        $summaryHtml = str_replace('TOTAL_RECOVERED', round($totalRecovered, 2), $summaryHtml);

        file_put_contents($path, $summaryHtml, FILE_APPEND);
    }

    private function appendReport(string $content): void
    {
        // kept for backward compat - no longer used
    }
}
