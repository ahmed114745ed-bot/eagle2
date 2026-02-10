<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\FairLuckService2;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SimulateLuckyGiftsDual extends Command
{
    protected $signature = 'simulate:lucky-gifts-dual {gift_id?} {--trials=1000}';
    protected $description = 'Simulate the Dual Algorithm system (Macro/Micro) and generate an HTML report';

    public function handle(FairLuckService2 $fairLuckService)
    {
        $maxTrials = (int) $this->option('trials');
        
        // Find a lucky gift
        $giftQuery = Gift::where('type', 6)->where('enable', 1);
        $giftId = $this->argument('gift_id') ?? $giftQuery->first()?->id;

        if (!$giftId) {
            $this->error('No lucky gift found.');
            return 1;
        }

        $gift = Gift::with('luckyGift')->find($giftId);
        
        $initialBalance = 1000000;
        $user = User::factory()->create([
            'di' => $initialBalance,
            'name' => 'DualAlgoPlayer_' . now()->timestamp,
        ]);

        $profile = \App\Models\UserLuckProfile::firstOrCreate(['user_id' => $user->id]);
        $profile->is_legacy_user = true; 
        $profile->save();

        $this->info("👤 User: {$user->name} (DUAL ALGO) | Initial Balance: {$initialBalance} | Gift: {$gift->name}");

        $history = [];
        $totalBetsCount = 0;
        $totalWonCoins = 0;
        $totalSpentCoins = 0;
        $currentBalance = $initialBalance;

        while ($currentBalance >= $gift->price && $totalBetsCount < $maxTrials) { 
            $balanceBefore = $currentBalance;
            $currentBalance -= $gift->price;
            $totalSpentCoins += $gift->price;
            $totalBetsCount++;

            // Process via FairLuckService2
            $result = $fairLuckService->processBet($user, $gift, $gift->price);
            
            $winAmount = $result->isWinner ? ($result->multiplier * $gift->price) : 0;
            $currentBalance += $winAmount;
            $totalWonCoins += $winAmount;

            $history[] = [
                'step' => $totalBetsCount,
                'balance_before' => $balanceBefore,
                'outcome' => $result->isWinner ? 'WIN' : 'LOSS',
                'multiplier' => $result->isWinner ? $result->multiplier . 'x' : '-',
                'win_amount' => $winAmount,
                'balance_after' => $currentBalance,
                'deviation' => number_format($result->newDeviation, 4),
                'mode' => $result->isRecovery ? 'RECOVERY 🛠️' : 'MACRO 🚀',
            ];

            $user->di = $currentBalance;
            $user->save();
        }

        $this->generateHtmlReport($user, $gift, $initialBalance, $totalBetsCount, $totalSpentCoins, $totalWonCoins, $currentBalance, $history);

        $this->info("---------------------------------------");
        $this->info("🏁 Dual Algo Simulation Finished!");
        $this->info("Final Balance: " . number_format($currentBalance) . " coins");
        $this->info("---------------------------------------");

        return 0;
    }

    private function generateHtmlReport($user, $gift, $initial, $count, $spent, $won, $final, $history)
    {
        $rtp = $spent > 0 ? ($won / $spent) * 100 : 0;
        $net = $final - $initial;
        $netClass = $net >= 0 ? 'text-success' : 'text-danger';

        $html = "
        <!DOCTYPE html>
        <html lang='ar' dir='rtl'>
        <head>
            <meta charset='UTF-8'>
            <title>تقرير النظام المزدوج - {$user->name}</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                .win-row { background-color: #e8f5e9 !important; }
                .recovery-row { background-color: #fff3e0 !important; }
                .badge-macro { background-color: #0d6efd; color: white; }
                .badge-recovery { background-color: #fd7e14; color: white; }
            </style>
        </head>
        <body>
            <div class='container py-5'>
                <h1 class='text-center mb-4'>📊 تقرير النظام المزدوج (Dual Algorithm)</h1>
                <div class='row text-center mb-4'>
                    <div class='col-md-3'><h5>الرصيد الابتدائي</h5><p class='h4'>" . number_format($initial) . "</p></div>
                    <div class='col-md-3'><h5>الرصيد النهائي</h5><p class='h4'>" . number_format($final) . "</p></div>
                    <div class='col-md-3'><h5>صافي النتيجة</h5><p class='h4 {$netClass}'>" . number_format($net) . "</p></div>
                    <div class='col-md-3'><h5>نسبة RTP</h5><p class='h4'>" . number_format($rtp, 2) . "%</p></div>
                </div>
                <div class='table-responsive'>
                    <table class='table table-bordered bg-white'>
                        <thead class='table-dark'>
                            <tr>
                                <th>#</th>
                                <th>الرصيد قبل</th>
                                <th>الوضع</th>
                                <th>النتيجة</th>
                                <th>المضاعف</th>
                                <th>المكسب</th>
                                <th>الرصيد بعد</th>
                                <th>الانحراف</th>
                            </tr>
                        </thead>
                        <tbody>";

        foreach ($history as $h) {
            $rowClass = $h['outcome'] === 'WIN' ? 'win-row' : '';
            if ($h['mode'] !== 'MACRO 🚀') $rowClass = 'recovery-row';
            $badgeClass = str_contains($h['mode'], 'MACRO') ? 'badge-macro' : 'badge-recovery';
            
            $html .= "
                            <tr class='{$rowClass}'>
                                <td>{$h['step']}</td>
                                <td>" . number_format($h['balance_before']) . "</td>
                                <td><span class='badge {$badgeClass}'>{$h['mode']}</span></td>
                                <td>" . $h['outcome'] . "</td>
                                <td>" . $h['multiplier'] . "</td>
                                <td>" . number_format($h['win_amount']) . "</td>
                                <td>" . number_format($h['balance_after']) . "</td>
                                <td>{$h['deviation']}</td>
                            </tr>";
        }

        $html .= "
                        </tbody>
                    </table>
                </div>
            </div>
        </body>
        </html>";

        File::put(public_path('dual_lucky_report.html'), $html);
        $this->info("📍 Report generated: " . public_path('dual_lucky_report.html'));
    }
}
