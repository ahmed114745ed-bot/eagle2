<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\FairLuckService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SimulateLuckyGiftsHtml extends Command
{
    protected $signature = 'simulate:lucky-gifts-html {gift_id?}';
    protected $description = 'Simulate natural gameplay starting with 30,000 coins and generate an HTML report';

    public function handle(FairLuckService $fairLuckService)
    {
        $initialBalance = 30000;
        
        // Find a lucky gift
        $giftQuery = Gift::where('type', 6)->where('enable', 1);
        $giftId = $this->argument('gift_id') ?? $giftQuery->first()?->id;

        if (!$giftId) {
            $this->error('No lucky gift found.');
            return 1;
        }

        $gift = Gift::with('luckyGift')->find($giftId);
        
        $initialBalance = 1000000; // Increase balance to 1M to see 1000 trials clearly
        $user = User::factory()->create([
            'di' => $initialBalance,
            'name' => 'VeteranPlayer_' . now()->timestamp,
        ]);

        // Force this user to be a "NON-BEGINNER" (Legacy User)
        // This ensures they don't get the beginner boost probabilities
        $profile = \App\Models\UserLuckProfile::firstOrCreate(['user_id' => $user->id]);
        $profile->is_legacy_user = true; 
        $profile->bet_count = 1000;
        $profile->total_bets = 1500000; // Legacy 1.5M bets
        $profile->total_profit = -15000; // Perfect 1% loss history
        $profile->first_bet_at = now()->subDays(10);
        $profile->save();

        $this->info("👤 User: {$user->name} (VETERAN) | Initial Balance: {$initialBalance} | Gift: {$gift->name} ({$gift->price})");

        $history = [];
        $totalBetsCount = 0;
        $totalWonCoins = 0;
        $totalSpentCoins = 0;
        $currentBalance = $initialBalance;

        $maxTrials = 1000; 

        while ($currentBalance >= $gift->price && $totalBetsCount < $maxTrials) { 
            $balanceBefore = $currentBalance;
            
            // Deduct before bet (natural play)
            $currentBalance -= $gift->price;
            $totalSpentCoins += $gift->price;
            $totalBetsCount++;

            // Process via FairLuck
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
            ];

            // Manually update user balance to keep it synced with FairLuckService internal logic if it reads from DB
            $user->di = $currentBalance;
            $user->save();
        }

        $this->generateHtmlReport($user, $gift, $initialBalance, $totalBetsCount, $totalSpentCoins, $totalWonCoins, $currentBalance, $history);

        $this->info("---------------------------------------");
        $this->info("🏁 Simulation Finished!");
        $this->info("Total Bets: " . $totalBetsCount);
        $this->info("Final Balance: " . number_format($currentBalance) . " coins");
        $this->info("Net Result: " . number_format($currentBalance - $initialBalance) . " coins");
        $this->info("---------------------------------------");

        // Cleanup
        // DB::table('fair_luck_transactions')->where('user_id', $user->id)->delete();
        // DB::table('user_luck_profiles')->where('user_id', $user->id)->delete();
        // $user->delete();

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
            <title>تقرير حظ المستخدم - {$user->name}</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 30px; }
                .table-container { background: white; border-radius: 15px; padding: 20px; margin-top: 20px; }
                .win-row { background-color: #d4edda !important; }
                .loss-row { background-color: #f8d7da !important; }
                .badge-win { background-color: #28a745; color: white; }
                .badge-loss { background-color: #dc3545; color: white; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='card p-4'>
                    <h1 class='text-center mb-4'>📊 تقرير محاكاة حظ المستخدم (طبيعي)</h1>
                    <div class='row text-center'>
                        <div class='col-md-3'>
                            <h5>الرصيد الابتدائي</h5>
                            <p class='h4 text-primary'>".number_format($initial)."</p>
                        </div>
                        <div class='col-md-3'>
                            <h5>إجمالي الرهانات</h5>
                            <p class='h4'>".number_format($count)."</p>
                        </div>
                        <div class='col-md-3'>
                            <h5>الرصيد النهائي</h5>
                            <p class='h4'>".number_format($final)."</p>
                        </div>
                        <div class='col-md-3'>
                            <h5>صافي الربح/الخسارة</h5>
                            <p class='h4 {$netClass}'>".number_format($net)."</p>
                        </div>
                    </div>
                    <div class='row text-center mt-3'>
                        <div class='col-md-4'>
                            <h5>إجمالي المصروف</h5>
                            <p class='h4 text-danger'>".number_format($spent)."</p>
                        </div>
                        <div class='col-md-4'>
                            <h5>إجمالي الأرباح</h5>
                            <p class='h4 text-success'>".number_format($won)."</p>
                        </div>
                        <div class='col-md-4'>
                            <h5>معدل العائد (RTP)</h5>
                            <p class='h4'>".number_format($rtp, 2)."%</p>
                        </div>
                    </div>
                </div>

                <div class='table-container shadow'>
                    <h3 class='mb-3'>📜 تفاصيل كل الضربات</h3>
                    <table class='table table-hover text-center'>
                        <thead class='table-dark'>
                            <tr>
                                <th>الضربه #</th>
                                <th>الرصيد قبل</th>
                                <th>النتيجة</th>
                                <th>المضاعف</th>
                                <th>قيمة الفوز</th>
                                <th>الرصيد بعد</th>
                                <th>الانحراف</th>
                            </tr>
                        </thead>
                        <tbody>";

                foreach ($history as $row) {
                    $class = $row['outcome'] === 'WIN' ? 'win-row' : 'loss-row';
                    $badge = $row['outcome'] === 'WIN' ? 'badge-win' : 'badge-loss';
                    $html .= "
                            <tr class='{$class}'>
                                <td>{$row['step']}</td>
                                <td>".number_format($row['balance_before'])."</td>
                                <td><span class='badge {$badge}'>{$row['outcome']}</span></td>
                                <td>{$row['multiplier']}</td>
                                <td>".number_format($row['win_amount'])."</td>
                                <td>".number_format($row['balance_after'])."</td>
                                <td>{$row['deviation']}</td>
                            </tr>";
                }

                $html .= "
                        </tbody>
                    </table>
                </div>
            </div>
        </body>
        </html>";

                $filePath = public_path('lucky_report.html');
                File::put($filePath, $html);
                $this->info("\n✅ تم إنشاء التقرير بنجاح!");
                $this->info("📍 المسار: " . $filePath);
                $this->info("🔗 يمكنك فتحه في المتصفح لرؤية النتائج.");
    }
}
