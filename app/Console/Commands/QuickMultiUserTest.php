<?php

namespace App\Console\Commands;

use App\Models\FairLuckWallet;
use App\Models\User;
use App\Models\Gift;
use App\Services\FairLuck\FairLuckService3;
use App\Services\FairLuck\DeviationCalculator;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Console\Command;

class QuickMultiUserTest extends Command
{
    protected $signature = 'fairluck:quick-multi 
                            {gift_id=383 : معرف الهدية} 
                            {bet_amount=100 : مبلغ الرهان}
                            {--users=3 : عدد المستخدمين}
                            {--initial_balance=1000 : الرصيد الابتدائي}
                            {--max_rounds=5000 : الحد الأقصى للأدوار}
                            {--force_completion : إجبار إكمال حتى انتهاء جميع الأرصدة}';

    protected $description = 'تقرير شامل متعدد المستخدمين مع تفاصيل كاملة للمحافظ';

    private array $users = [];
    private array $allRounds = [];
    private array $walletHistory = [];
    private int $totalRounds = 0;
    private float $totalAppProfit = 0;

    public function handle()
    {
        $giftId = $this->argument('gift_id');
        $betAmount = (float) $this->argument('bet_amount');
        $userCount = (int) $this->option('users');
        $initialBalance = (float) $this->option('initial_balance');
        $maxRounds = (int) $this->option('max_rounds');
        $forceCompletion = $this->option('force_completion');

        $this->info("🎯 تقرير شامل مع {$userCount} مستخدمين (حد أقصى {$maxRounds} دور)");
        if ($forceCompletion) {
            $this->info("⚡ وضع الإجبار: سيستمر حتى انتهاء جميع الأرصدة");
        }
        
        $gift = Gift::find($giftId);
        if (!$gift) {
            $this->error('❌ الهدية غير موجودة');
            return;
        }

        // إنشاء المستخدمين
        $this->createTestUsers($userCount, $initialBalance);
        
        // تشغيل الاختبار السريع
        $this->runQuickTest($gift, $betAmount, $maxRounds);
        
        // توليد تقرير مبسط
        $this->generateQuickReport($gift, $betAmount, $initialBalance);
    }

    private function createTestUsers($userCount, $initialBalance)
    {
        for ($i = 1; $i <= $userCount; $i++) {
            $letter = chr(64 + $i); // A, B, C, etc.
            $username = "QuickTest_{$letter}_" . time() . rand(100, 999);
            
            $user = User::create([
                'name' => $username,
                'email' => strtolower($letter) . '_quick_' . time() . rand(100,999) . '@test.local',
                'password' => bcrypt('password'),
                'di' => $initialBalance,
                'email_verified_at' => now()
            ]);
            
            $this->users[] = [
                'user' => $user,
                'letter' => $letter,
                'initial_balance' => $initialBalance,
                'attempts' => 0,
                'wins' => 0,
                'total_bet' => 0,
                'total_win' => 0,
                'final_balance' => $initialBalance,
                'finished_round' => 0,
                'max_multiplier' => 0,
                'high_multipliers' => 0,
                'net_result' => 0,
                'app_cut' => 0
            ];
        }
    }

    private function runQuickTest($gift, $betAmount, $maxRounds)
    {
        $fairLuckService = app(FairLuckService3::class);
        $round = 1;
        $activeUsers = count($this->users);
        
        $progressBar = $this->output->createProgressBar($maxRounds);
        $progressBar->start();
        
        while ($activeUsers > 0 && $round <= $maxRounds) {
            foreach ($this->users as $index => &$userData) {
                if ($userData['user']->di < $betAmount) {
                    continue;
                }
                
                try {
                    $result = $fairLuckService->processBet($userData['user'], $gift, $betAmount);
                    
                    $userData['user']->refresh();
                    $userData['attempts']++;
                    $userData['total_bet'] += $betAmount;
                    
                    if ($result->isWinner) {
                        $userData['wins']++;
                        $userData['total_win'] += $result->profitAmount;
                        if ($result->multiplier > $userData['max_multiplier']) {
                            $userData['max_multiplier'] = $result->multiplier;
                        }
                        if ($result->multiplier >= 250) {
                            $userData['high_multipliers']++;
                        }
                    }
                    
                    $userData['final_balance'] = $userData['user']->di;
                    
                    if ($userData['user']->di < $betAmount && $userData['finished_round'] == 0) {
                        $userData['finished_round'] = $round;
                    }
                    
                } catch (\Exception $e) {
                    // تجاهل الأخطاء في الاختبار السريع
                }
            }
            
            // حساب المستخدمين النشطين
            $activeUsers = 0;
            foreach ($this->users as $userData) {
                if ($userData['user']->di >= $betAmount) {
                    $activeUsers++;
                }
            }
            
            $round++;
            $progressBar->advance();
            
            // كسر مبكر إذا انتهى الجميع
            if ($activeUsers == 0) {
                break;
            }
        }
        
        $progressBar->finish();
        $this->line('');
        
        $this->totalRounds = $round - 1;
        
        // تحديث الدور النهائي للمستخدمين المتبقين
        foreach ($this->users as &$userData) {
            if ($userData['finished_round'] == 0) {
                $userData['finished_round'] = $this->totalRounds;
            }
        }
    }

    private function generateQuickReport($gift, $betAmount, $initialBalance)
    {
        $this->info("\n🎯 نتائج الاختبار السريع:");
        $this->info("إجمالي الأدوار: {$this->totalRounds}");
        
        $summaryData = [];
        $totalAttempts = 0;
        $totalWins = 0;
        $totalBets = 0;
        $totalWinnings = 0;
        
        foreach ($this->users as $userData) {
            $rtp = $userData['total_bet'] > 0 ? ($userData['total_win'] / $userData['total_bet']) * 100 : 0;
            $netResult = $userData['total_win'] - $userData['total_bet'];
            
            $summaryData[] = [
                'المستخدم' => $userData['letter'],
                'المحاولات' => number_format($userData['attempts']),
                'المكاسب' => number_format($userData['wins']),
                'RTP' => number_format($rtp, 1) . '%',
                'الرصيد النهائي' => number_format($userData['final_balance']),
                'الدور النهائي' => $userData['finished_round'],
                'أعلى مضاعف' => $userData['max_multiplier'] . 'x',
                'مضاعفات عالية' => $userData['high_multipliers']
            ];
            
            $totalAttempts += $userData['attempts'];
            $totalWins += $userData['wins'];
            $totalBets += $userData['total_bet'];
            $totalWinnings += $userData['total_win'];
        }
        
        $this->table(array_keys($summaryData[0]), array_map('array_values', $summaryData));
        
        $overallRTP = $totalBets > 0 ? ($totalWinnings / $totalBets) * 100 : 0;
        $this->info("📊 RTP الإجمالي: " . number_format($overallRTP, 2) . '%');
        $this->info("📊 إجمالي المحاولات: " . number_format($totalAttempts));
        $this->info("📊 إجمالي المكاسب: " . number_format($totalWins));
        
        // توليد ملف HTML مبسط
        $this->generateSimpleHtml($gift, $betAmount, $initialBalance);
    }

    private function generateSimpleHtml($gift, $betAmount, $initialBalance)
    {
        $timestamp = date('Y_m_d_H_i_s');
        $filename = "quick_multi_test_{$timestamp}.html";
        
        $totalAttempts = array_sum(array_column($this->users, 'attempts'));
        $totalWins = array_sum(array_column($this->users, 'wins'));
        $totalBets = array_sum(array_column($this->users, 'total_bet'));
        $totalWinnings = array_sum(array_column($this->users, 'total_win'));
        $overallRTP = $totalBets > 0 ? ($totalWinnings / $totalBets) * 100 : 0;
        
        $html = "<!DOCTYPE html>
<html dir='rtl' lang='ar'>
<head>
    <meta charset='UTF-8'>
    <title>اختبار سريع - FairLuck متعدد المستخدمين</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }
        .header { background: linear-gradient(45deg, #ff6b6b, #4ecdc4); color: white; padding: 40px; text-align: center; }
        .stats-card { background: white; border-radius: 10px; padding: 20px; margin: 10px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class='container-fluid'>
        <div class='header'>
            <h1>⚡ اختبار سريع - FairLuck متعدد المستخدمين</h1>
            <p>الهدية: {$gift->name} | مبلغ الرهان: " . number_format($betAmount) . " | إجمالي الأدوار: {$this->totalRounds}</p>
        </div>
        
        <div class='container mt-4'>
            <div class='row'>
                <div class='col-md-3'>
                    <div class='stats-card text-center'>
                        <h4>" . count($this->users) . "</h4>
                        <small>عدد المستخدمين</small>
                    </div>
                </div>
                <div class='col-md-3'>
                    <div class='stats-card text-center'>
                        <h4>" . number_format($totalAttempts) . "</h4>
                        <small>إجمالي المحاولات</small>
                    </div>
                </div>
                <div class='col-md-3'>
                    <div class='stats-card text-center'>
                        <h4>" . number_format($totalWins) . "</h4>
                        <small>إجمالي المكاسب</small>
                    </div>
                </div>
                <div class='col-md-3'>
                    <div class='stats-card text-center'>
                        <h4>" . number_format($overallRTP, 2) . "%</h4>
                        <small>RTP الإجمالي</small>
                    </div>
                </div>
            </div>
            
            <div class='stats-card mt-4'>
                <h3>نتائج المستخدمين</h3>
                <div class='table-responsive'>
                    <table class='table table-striped'>
                        <thead class='table-dark'>
                            <tr>
                                <th>المستخدم</th>
                                <th>المحاولات</th>
                                <th>المكاسب</th>
                                <th>RTP</th>
                                <th>الرصيد النهائي</th>
                                <th>الدور النهائي</th>
                                <th>أعلى مضاعف</th>
                            </tr>
                        </thead>
                        <tbody>";
        
        foreach ($this->users as $userData) {
            $rtp = $userData['total_bet'] > 0 ? ($userData['total_win'] / $userData['total_bet']) * 100 : 0;
            $html .= "<tr>
                        <td>{$userData['letter']}</td>
                        <td>" . number_format($userData['attempts']) . "</td>
                        <td>" . number_format($userData['wins']) . "</td>
                        <td>" . number_format($rtp, 1) . "%</td>
                        <td>" . number_format($userData['final_balance']) . "</td>
                        <td>{$userData['finished_round']}</td>
                        <td>{$userData['max_multiplier']}x</td>
                      </tr>";
        }
        
        $html .= "</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
        
        // حفظ الملف
        $fullPath = storage_path("app/public/reports/{$filename}");
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        file_put_contents($fullPath, $html);
        
        // نسخ إلى public
        $publicPath = public_path("quick_multi_test.html");
        copy($fullPath, $publicPath);
        
        $this->info("📄 تقرير HTML: {$publicPath}");
    }
}