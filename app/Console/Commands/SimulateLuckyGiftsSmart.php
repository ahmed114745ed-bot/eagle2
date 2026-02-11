<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\FairLuckService3;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SimulateLuckyGiftsSmart extends Command
{
    protected $signature = 'simulate:lucky-gifts-smart {gift_id?} {--trials=1000} {--balance=30000} {--userId=}';
    protected $description = 'Simulate the Smart Hybrid Algorithm (System 3) and generate an HTML report';

    public function handle(FairLuckService3 $fairLuckService)
    {
        $initialBalance = (int) $this->option('balance');
        $maxTrials = (int) $this->option('trials');
        $userId = $this->option('userId');
        
        // Find a lucky gift
        $giftQuery = Gift::where('type', 6)->where('enable', 1);
        $giftId = $this->argument('gift_id') ?? $giftQuery->first()?->id;

        if (!$giftId) {
            $this->error('No lucky gift found.');
            return 1;
        }

        $gift = Gift::with('luckyGift')->find($giftId);
        
        if ($userId) {
            $user = User::findOrFail($userId);
            $user->di = $initialBalance;
            $user->save();
        } else {
            $user = User::factory()->create([
                'di' => $initialBalance,
                'name' => 'SmartPlayer_' . now()->timestamp,
            ]);
        }

        $profile = \App\Models\UserLuckProfile::firstOrCreate(['user_id' => $user->id]);
        $profile->is_legacy_user = true; 
        $profile->save();

        $this->info("👤 User: {$user->name} (SYSTEM 3) | Initial Balance: {$initialBalance} | Gift: {$gift->name}");

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

            // Process via FairLuckService3
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
                'mood' => $result->mood,
            ];

            $user->di = $currentBalance;
            $user->save();
        }

        $this->generateHtmlReport($user, $gift, $initialBalance, $totalBetsCount, $totalSpentCoins, $totalWonCoins, $currentBalance, $history);

        $this->info("---------------------------------------");
        $this->info("🏁 Smart Simulation Finished!");
        $this->info("Final Balance: " . number_format($currentBalance));
        $this->info("RTP: " . number_format(($totalWonCoins / ($totalSpentCoins ?: 1)) * 100, 2) . "%");
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
            <title>تقرير النظام الذكي - {$user->name}</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f0f2f5; font-family: 'Segoe UI', serif; }
                .win-row { background-color: #d1e7dd !important; }
                .mood-Recovery { color: #dc3545; font-weight: bold; }
                .mood-Generous { color: #198754; font-weight: bold; }
                .mood-Stable { color: #0d6efd; }
            </style>
        </head>
        <body>
            <div class='container py-5'>
                <h1 class='text-center mb-4'>🧠 تقرير النظام الهجين الذكي (Service 3)</h1>
                <div class='row text-center mb-4 card p-4 shadow-sm'>
                   <div class='col-12 row'>
                        <div class='col'><h5>الرصيد الابتدائي</h5><p class='h3 text-secondary'>" . number_format($initial) . "</p></div>
                        <div class='col'><h5>إجمالي المدفوع</h5><p class='h3 text-danger'>" . number_format($spent) . "</p></div>
                        <div class='col'><h5>إجمالي المكسب</h5><p class='h3 text-success'>" . number_format($won) . "</p></div>
                        <div class='col'><h5>الرصيد النهائي</h5><p class='h3'>" . number_format($final) . "</p></div>
                        <div class='col'><h5>صافي النتجية</h5><p class='h3 {$netClass}'>" . number_format($net) . "</p></div>
                        <div class='col'><h5>المراهنات</h5><p class='h4'>" . number_format($count) . "</p></div>
                        <div class='col'><h5>نسبة RTP</h5><p class='h4'>" . number_format($rtp, 2) . "%</p></div>
                   </div>
                </div>
                <div class='table-responsive card shadow'>
                    <table class='table table-hover mb-0'>
                        <thead class='table-dark'>
                            <tr>
                                <th>الخطوة</th>
                                <th>الحالة (Mood)</th>
                                <th>النتيجة</th>
                                <th>المضاعف</th>
                                <th>المكسب</th>
                                <th>الرصيد</th>
                                <th>الانحراف</th>
                            </tr>
                        </thead>
                        <tbody>";

        foreach ($history as $h) {
            $rowClass = $h['outcome'] === 'WIN' ? 'win-row' : '';
            
            $html .= "
                            <tr class='{$rowClass}'>
                                <td>{$h['step']}</td>
                                <td class='mood-{$h['mood']}'>{$h['mood']}</td>
                                <td>" . ($h['outcome'] == 'WIN' ? '✅ فوز' : '❌ خسارة') . "</td>
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

        File::put(public_path('smart_lucky_report.html'), $html);
        $this->info("📍 Report generated: " . public_path('smart_lucky_report.html'));
    }
}
