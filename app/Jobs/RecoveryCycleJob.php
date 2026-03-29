<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecoveryCycleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    protected $targetMonth = 3;
    protected $targetYear = 2026;
    protected $maxCycles = 10;
    protected $reportFile = 'recovery_cycle_report.html';

    public function handle()
    {
        ini_set('memory_limit', '1G');

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }
        DB::disableQueryLog();
        DB::statement("SET SESSION innodb_lock_wait_timeout = 120");

        $this->initReport();

        for ($cycle = 1; $cycle <= $this->maxCycles; $cycle++) {
            $this->appendCycleHeader($cycle);
            Log::info("RecoveryCycle: Starting cycle {$cycle}");

            // Step 1: User Recovery
            $userDebtors = $this->countUserDebtors();
            $this->appendStep("User Recovery", "Found {$userDebtors} user debtors");

            $processed = 0;
            while ($userDebtors > 0) {
                try {
                    $job = new DirectRecoveryJob();
                    $job->handle();
                    $processed++;
                } catch (\Exception $e) {
                    $this->appendStep("User Recovery Error", $e->getMessage(), true);
                    break;
                }
                $userDebtors = $this->countUserDebtors();
            }
            $this->appendStep("User Recovery", "Processed {$processed} users, remaining: {$userDebtors}");

            // Step 2: Agency Recovery
            $agencyDebtors = $this->countAgencyDebtors();
            $this->appendStep("Agency Recovery", "Found {$agencyDebtors} agency debtors");

            $processed = 0;
            while ($agencyDebtors > 0) {
                try {
                    $job = new AgencyRecoveryJob();
                    $job->handle();
                    $processed++;
                } catch (\Exception $e) {
                    $this->appendStep("Agency Recovery Error", $e->getMessage(), true);
                    break;
                }
                $agencyDebtors = $this->countAgencyDebtors();
            }
            $this->appendStep("Agency Recovery", "Processed {$processed} agencies, remaining: {$agencyDebtors}");

            // Step 3: Recalculate Salaries
            $this->appendStep("Calculate Salary", "Recalculating salaries for all users...");
            $this->recalculateSalaries();
            $this->appendStep("Calculate Salary", "Done");

            // Step 4: Check if still negative
            $newUserDebtors = $this->countUserDebtors();
            $newAgencyDebtors = $this->countAgencyDebtors();
            $this->appendStep("Check", "After recalc: {$newUserDebtors} user debtors, {$newAgencyDebtors} agency debtors");

            if ($newUserDebtors == 0 && $newAgencyDebtors == 0) {
                $this->appendStep("DONE", "All salaries are clean! Completed in {$cycle} cycle(s).");
                Log::info("RecoveryCycle: All clean after {$cycle} cycles.");
                break;
            }

            if ($cycle == $this->maxCycles) {
                $this->appendStep("MAX CYCLES", "Reached max {$this->maxCycles} cycles. Still {$newUserDebtors} user + {$newAgencyDebtors} agency debtors remaining.");
            }
        }

        $this->finishReport();
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
                Log::warning("RecoveryCycle: Salary calc failed for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    private function initReport(): void
    {
        $path = public_path($this->reportFile);
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recovery Cycle Report</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #0f172a; color: #e2e8f0; padding: 20px; }
.header { text-align: center; padding: 30px; background: linear-gradient(135deg, #1e293b, #334155); border-radius: 16px; margin-bottom: 30px; border: 1px solid #475569; }
.header h1 { font-size: 28px; color: #a78bfa; margin-bottom: 8px; }
.header .date { color: #94a3b8; font-size: 14px; }
.cycle { background: #1e293b; border-radius: 12px; margin-bottom: 20px; border: 1px solid #334155; padding: 20px; }
.cycle-title { font-size: 20px; font-weight: 700; color: #38bdf8; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #334155; }
.step { padding: 8px 16px; margin-bottom: 6px; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
.step-normal { background: #1a2332; border-left: 3px solid #38bdf8; }
.step-error { background: #2a1215; border-left: 3px solid #f87171; }
.step-success { background: #132a1e; border-left: 3px solid #4ade80; }
.step-label { font-weight: 600; min-width: 160px; color: #94a3b8; }
.step-text { color: #e2e8f0; }
.summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 30px; }
.stat-card { background: #1e293b; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #334155; }
.stat-value { font-size: 36px; font-weight: 700; margin-bottom: 4px; }
.stat-label { font-size: 13px; color: #94a3b8; }
</style>
</head>
<body>
<div class="header">
<h1>Recovery Cycle Report</h1>
<div class="date">Started: REPORT_DATE</div>
</div>
<div id="cycles">
HTML;
        $html = str_replace('REPORT_DATE', now()->toDateTimeString(), $html);
        file_put_contents($path, $html);
    }

    private function appendCycleHeader(int $cycle): void
    {
        $path = public_path($this->reportFile);
        $html = "<div class=\"cycle\"><div class=\"cycle-title\">Cycle #{$cycle} - " . now()->toDateTimeString() . "</div>\n";
        file_put_contents($path, $html, FILE_APPEND);
    }

    private function appendStep(string $label, string $text, bool $isError = false): void
    {
        $path = public_path($this->reportFile);
        $class = $isError ? 'step-error' : (str_contains(strtolower($label), 'done') ? 'step-success' : 'step-normal');
        $label = htmlspecialchars($label);
        $text = htmlspecialchars(substr($text, 0, 300));
        $html = "<div class=\"step {$class}\"><span class=\"step-label\">{$label}</span><span class=\"step-text\">{$text}</span></div>\n";
        file_put_contents($path, $html, FILE_APPEND);
    }

    private function finishReport(): void
    {
        $path = public_path($this->reportFile);

        $userDebtors = $this->countUserDebtors();
        $agencyDebtors = $this->countAgencyDebtors();

        $html = <<<HTML
</div></div>
<div class="summary-grid">
<div class="stat-card">
<div class="stat-value" style="color: #38bdf8;">{$userDebtors}</div>
<div class="stat-label">Remaining User Debtors</div>
</div>
<div class="stat-card">
<div class="stat-value" style="color: #f59e0b;">{$agencyDebtors}</div>
<div class="stat-label">Remaining Agency Debtors</div>
</div>
<div class="stat-card">
<div class="stat-value" style="color: #4ade80;">FINISH_TIME</div>
<div class="stat-label">Completed At</div>
</div>
</div>
</body>
</html>
HTML;
        $html = str_replace('FINISH_TIME', now()->format('H:i:s'), $html);
        file_put_contents($path, $html, FILE_APPEND);
    }
}
