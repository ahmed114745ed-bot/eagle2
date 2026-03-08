<?php

namespace App\Console\Commands;


use App\Models\FairLuckWallet;
use App\Models\User;
use App\Models\Gift;
use App\Services\FairLuck\FairLuckService3;
use App\Services\FairLuck\DeviationCalculator;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Console\Command;

class DetailedMultiUserReport extends Command
{
    protected $signature = 'fairluck:detailed-multi 
                            {gift_id=383 : معرف الهدية} 
                            {bet_amount=100 : مبلغ الرهان}
                            {--users=3 : عدد المستخدمين}
                            {--initial_balance=1000 : الرصيد الابتدائي}
                            {--max_rounds=3000 : الحد الأقصى للأدوار}
                            {--force_completion : إجبار الإكمال حتى انتهاء جميع الأرصدة}';

    protected $description = 'تقرير شامل متعدد المستخدمين مع تفاصيل كاملة للمحافظ وفلاتر';

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
        $forceCompletion = $this->option('force_completion');

        $this->info("🎯 تقرير شامل مع {$userCount} مستخدمين");
        $this->info("💰 الرصيد الابتدائي: " . number_format($initialBalance) . " | مبلغ الرهان: " . number_format($betAmount));
        $maxRounds = (int) $this->option('max_rounds');
        $this->info("⏰ حد أقصى: {$maxRounds} دور");
        
        $gift = Gift::find($giftId);
        if (!$gift) {
            $this->error('❌ الهدية غير موجودة');
            return;
        }

        // إنشاء المستخدمين
        $this->createTestUsers($userCount, $initialBalance);
        
        // تشغيل المحاكاة الشاملة
        $this->runDetailedSimulation($gift, $betAmount, $forceCompletion);
        
        // توليد تقرير HTML مفصل
        $this->generateDetailedHtmlReport($gift, $betAmount, $initialBalance);
        
        // عرض النتائج
        $this->displayResults();
    }

    private function createTestUsers($userCount, $initialBalance)
    {
        for ($i = 1; $i <= $userCount; $i++) {
            $letter = chr(64 + $i); // A, B, C, etc.
            $username = "DetailedUser_{$letter}_" . time() . rand(100, 999);
            
            $user = User::create([
                'name' => $username,
                'email' => strtolower($letter) . '_detail_' . time() . rand(100,999) . '@test.local',
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

    private function runDetailedSimulation($gift, $betAmount, $forceCompletion)
    {
        $fairLuckService = app(FairLuckService3::class);
        $round = 1;
        $activeUsers = count($this->users);
        
        // تصفير جميع المحافظ في البداية
        $this->info("🔄 تصفير المحافظ...");
        FairLuckWallet::where('wallet_type', 'global_vault')->update(['balance' => 0]);
        FairLuckWallet::where('wallet_type', 'jackpot_wallet')->update(['balance' => 0]);
        FairLuckWallet::where('wallet_type', 'medium_wallet')->update(['balance' => 0]);
        
        // حفظ الحالة الأولية للمحافظ (بعد التصفير)
        $this->walletHistory[0] = FairLuckWallet::getAllBalances();
        $this->info("✅ تم تصفير جميع المحافظ");
        
        $this->info("🎲 بدء المحاكاة...");
        
        while ($activeUsers > 0) {
            // عرض التقدم كل 50 دور
            if ($round % 50 == 0) {
                $this->line("الدور #{$round} - مستخدمين نشطين: {$activeUsers}");
            }
            
            foreach ($this->users as $index => &$userData) {
                if ($userData['user']->di < $betAmount) {
                    continue;
                }
                
                try {
                    // الحالة قبل الرهان
                    $walletsBefore = FairLuckWallet::getAllBalances();
                    $profileManager = app(ProfileManager::class);
                    $profileBefore = $profileManager->getProfile($userData['user']->id);
                    
                    $calculator = new DeviationCalculator();
                    $deviationBefore = $calculator->calculate(
                        $profileBefore->total_bets,
                        $profileBefore->total_profit,
                        $profileBefore->medium_wallet_wins ?? 0,
                        $profileBefore->jackpot_wallet_wins ?? 0
                    );
                    
                    // تنفيذ الرهان
                    $result = $fairLuckService->processBet($userData['user'], $gift, $betAmount);
                    
                    $userBalanceBefore = $userData['user']->di;
                    $actualProfit = $result->isWinner ? $result->profitAmount : -$betAmount;
                    $newBalance = $userBalanceBefore + $actualProfit;
                    
                    // تحديث قاعدة البيانات
                    $userData['user']->di = $newBalance;
                    $userData['user']->save();
                    
                    // إعادة تحديث للتأكد
                    $userData['user']->refresh();
                    
                    // تحديث بيانات المستخدم
                    $userBalanceAfter = $userData['user']->di;
                    $actualBalanceChange = $userBalanceAfter - $userBalanceBefore;
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
                    
                    $userData['final_balance'] = $userBalanceAfter;
                    $userData['net_result'] = $userData['total_win'] - $userData['total_bet'];
                    $userData['app_cut'] += $result->houseCut;
                    
                    // الحالة بعد الرهان
                    $walletsAfter = FairLuckWallet::getAllBalances();
                    
                    // حساب تغييرات المحافظ
                    $walletChanges = [
                        'global_vault' => $walletsAfter['global_vault'] - $walletsBefore['global_vault'],
                        'jackpot_wallet' => $walletsAfter['jackpot_wallet'] - $walletsBefore['jackpot_wallet'],
                        'medium_wallet' => $walletsAfter['medium_wallet'] - $walletsBefore['medium_wallet']
                    ];
                    
                    $this->totalAppProfit += ($betAmount - ($result->isWinner ? $result->profitAmount : 0));
                    
                    // حفظ تفاصيل الدور
                    $this->allRounds[] = [
                        'global_round' => $round,
                        'user_round' => $userData['attempts'],
                        'user' => $userData['user'],
                        'user_letter' => $userData['letter'],
                        'result' => $result,
                        'deviation_before' => $deviationBefore,
                        'deviation_after' => $result->newDeviation,
                        'wallets_before' => $walletsBefore,
                        'wallets_after' => $walletsAfter,
                        'wallet_changes' => $walletChanges,
                        'bet_amount' => $betAmount,
                        'user_balance_before' => $userBalanceBefore,
                        'user_balance_after' => $userBalanceAfter,
                        'user_balance_change' => $actualBalanceChange,
                        'user_balance' => $userBalanceAfter,
                        'total_app_profit' => $this->totalAppProfit,
                        'total_player_winnings' => array_sum(array_column($this->users, 'total_win'))
                    ];
                    
                    if ($userData['user']->di < $betAmount && $userData['finished_round'] == 0) {
                        $userData['finished_round'] = $round;
                        $this->line("💸 انتهى رصيد المستخدم {$userData['letter']} في الدور {$round}");
                    }
                    
                } catch (\Exception $e) {
                    $this->error("خطأ للمستخدم {$userData['letter']}: " . $e->getMessage());
                }
            }
            
            // حفظ حالة المحافظ بعد كل دور
            $this->walletHistory[$round] = FairLuckWallet::getAllBalances();
            
            // حساب المستخدمين النشطين
            $activeUsers = 0;
            foreach ($this->users as $userData) {
                if ($userData['user']->di >= $betAmount) {
                    $activeUsers++;
                }
            }
            
            $round++;
            
            // كسر إذا انتهى الجميع
            if ($activeUsers == 0) {
                $this->info("✅ انتهت جميع الأرصدة في الدور {$round}!");
                break;
            }
            
            // حد أمان - استخدام القيمة المُمررة من المعاملات
            $maxRounds = (int) $this->option('max_rounds');
            if ($round > $maxRounds) {
                $this->warn("⏰ تم الوصول للحد الأقصى ({$maxRounds} دور)");
                foreach ($this->users as &$userData) {
                    if ($userData['finished_round'] == 0) {
                        $userData['finished_round'] = $round;
                    }
                }
                break;
            }
        }
        
        $this->totalRounds = $round - 1;
    }

    private function generateDetailedHtmlReport($gift, $betAmount, $initialBalance)
    {
        $timestamp = date('Y_m_d_H_i_s');
        $filename = "detailed_fairluck_report_{$timestamp}.html";
        
        $totalAttempts = array_sum(array_column($this->users, 'attempts'));
        $totalWins = array_sum(array_column($this->users, 'wins'));
        $totalBets = array_sum(array_column($this->users, 'total_bet'));
        $totalWinnings = array_sum(array_column($this->users, 'total_win'));
        $overallRTP = $totalBets > 0 ? ($totalWinnings / $totalBets) * 100 : 0;
        
        $html = "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>تقرير FairLuck مفصل - متعدد المستخدمين</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background:#f8f9fb; font-family:'Segoe UI', serif; }
        h1, h2 { font-weight:700; }
        .table thead th { white-space:nowrap; }
        .user-card { transition: transform 0.3s; }
        .user-card:hover { transform: translateY(-5px); }
        .winner-row { background-color: #d4edda !important; }
        .loser-row { background-color: #f8d7da !important; }
        .filter-buttons { margin-bottom: 20px; }
        .stats-summary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .table small { font-size: 0.85em; }
    </style>
</head>
<body>
    <div class='container py-5'>
        <div class='stats-summary p-4 rounded mb-5'>
            <h1 class='text-center mb-4'>تقرير FairLuck مفصل - متعدد المستخدمين</h1>
            <p class='text-center mb-4'>الهدية: {$gift->name} | مبلغ الرهان: " . number_format($betAmount) . " | الرصيد الابتدائي: " . number_format($initialBalance) . " | إجمالي الأدوار: {$this->totalRounds}</p>
            <p class='text-center mb-2'><small>ملاحظة: تم تصفير جميع المحافظ في بداية المحاكاة</small></p>
        </div>

        <!-- بطاقات المستخدمين -->
        <div class='row mb-4'>";
        
        // تجنب تكرار المستخدمين - استخدم مصفوفة فريدة
        $uniqueUsers = [];
        foreach ($this->users as $userData) {
            $userId = $userData['user']->id;
            if (!isset($uniqueUsers[$userId])) {
                $uniqueUsers[$userId] = $userData;
            }
        }
        
        foreach ($uniqueUsers as $userData) {
            $rtp = $userData['total_bet'] > 0 ? ($userData['total_win'] / $userData['total_bet']) * 100 : 0;
            
            $html .= "<div class='col-md-4 mb-4'>
                <div class='card shadow-sm h-100 user-card'>
                    <div class='card-body'>
                        <h4 class='card-title'>المستخدم {$userData['user']->name} ({$userData['letter']})</h4>
                        <p class='mb-1 text-secondary'>الرصيد الابتدائي: " . number_format($initialBalance) . "</p>
                        <div class='row text-center g-3'>
                            <div class='col-6 col-lg-4'>
                                <small>المحاولات</small>
                                <p class='h5 mb-0'>{$userData['attempts']}</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>مرات الفوز</small>
                                <p class='h5 mb-0'>{$userData['wins']}</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>الدور النهائي</small>
                                <p class='h5 mb-0'>{$userData['finished_round']}</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>إجمالي الرهانات</small>
                                <p class='h5 mb-0'>" . number_format($userData['total_bet']) . "</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>إجمالي المكاسب</small>
                                <p class='h5 text-success mb-0'>" . number_format($userData['total_win']) . "</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>الرصيد النهائي</small>
                                <p class='h5 mb-0'>" . number_format($userData['final_balance']) . "</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>RTP</small>
                                <p class='h5 mb-0'>" . number_format($rtp, 2) . "%</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>مضاعفات ≥250</small>
                                <p class='h5 mb-0'>{$userData['high_multipliers']}</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>أعلى مضاعف</small>
                                <p class='h5 mb-0'>{$userData['max_multiplier']}x</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }
        
        $html .= "</div>

        <!-- الجدول التفصيلي مع الفلاتر -->
        <div class='card shadow-sm'>
            <div class='card-body'>
                <div class='row g-3 align-items-end mb-4'>
                    <div class='col-12 col-lg-8'>
                        <h2 class='h4 mb-1'>التفاصيل الكاملة للأدوار</h2>
                        <p class='text-muted mb-0'>جدول مفصل يُظهر كل رهان مع حالة المحافظ وتغييراتها في كل دور.</p>
                    </div>
                    <div class='col-12 col-lg-4'>
                        <label for='playerFilter' class='form-label small text-muted mb-1'>فلترة حسب المستخدم</label>
                        <select id='playerFilter' class='form-select form-select-sm'>
                            <option value='all'>الكل</option>";
        
        foreach ($this->users as $userData) {
            $html .= "<option value='{$userData['letter']}'>{$userData['user']->name} ({$userData['letter']})</option>";
        }
        
        $html .= "</select>
                    </div>
                </div>
                
                <div class='table-responsive'>
                    <table class='table table-striped align-middle small' data-filterable-table='true'>
                        <thead class='table-light'>
                            <tr>
                                <th>#</th>
                                <th>الدور</th>
                                <th>المستخدم</th>
                                <th>النتيجة</th>
                                <th>المضاعف</th>
                                <th>الرهان</th>
                                <th>الربح</th>
                                <th>تغيير رصيد المستخدم</th>
                                <th>الرصيد التراكمي</th>
                                <th>انحراف قبل</th>
                                <th>انحراف بعد</th>
                                <th>تغيير المحفظة الرئيسية</th>
                                <th>المحفظة الرئيسية تراكمي</th>
                                <th>تغيير محفظة الجاكبوت</th>
                                <th>محفظة الجاكبوت تراكمي</th>
                                <th>تغيير المحفظة المتوسطة</th>
                                <th>المحفظة المتوسطة تراكمي</th>
                                <th>صافي التطبيق</th>
                            </tr>
                        </thead>
                        <tbody>";
        
        foreach ($this->allRounds as $index => $round) {
            $isWinner = $round['result']->isWinner;
            $rowClass = $isWinner ? 'winner-row' : 'loser-row';
            $resultBadge = $isWinner ? "<span class='badge bg-success'>فوز</span>" : "<span class='badge bg-danger'>خسارة</span>";
            $multiplier = $isWinner ? $round['result']->multiplier . 'x' : '-';
            $profit = $isWinner ? number_format($round['result']->profitAmount) : '-' . number_format($round['bet_amount']);
            $profitClass = $isWinner ? 'text-success' : 'text-danger';
            
            // استخدام التغيير المحسوب مسبقاً
            $userBalanceChange = $round['user_balance_change'] ?? 0;
            
            $userBalanceChangeClass = $userBalanceChange >= 0 ? 'text-success' : 'text-danger';
            $userBalanceChangeText = ($userBalanceChange >= 0 ? '+' : '') . number_format($userBalanceChange);
            
            $html .= "<tr class='{$rowClass}' data-filterable-row='true' data-player='{$round['user_letter']}'>
                        <td>" . ($index + 1) . "</td>
                        <td>{$round['user_round']}</td>
                        <td>{$round['user']->name} ({$round['user_letter']})</td>
                        <td>{$resultBadge}</td>
                        <td>{$multiplier}</td>
                        <td>" . number_format($round['bet_amount']) . "</td>
                        <td class='{$profitClass}'>{$profit}</td>
                        <td class='{$userBalanceChangeClass}'>{$userBalanceChangeText}</td>
                        <td>" . number_format($round['user_balance']) . "</td>
                        <td>" . number_format($round['deviation_before'], 4) . "</td>
                        <td>" . number_format($round['deviation_after'], 4) . "</td>
                        <td class='" . ($round['wallet_changes']['global_vault'] >= 0 ? 'text-success' : 'text-danger') . "'>" . ($round['wallet_changes']['global_vault'] >= 0 ? '+' : '') . number_format($round['wallet_changes']['global_vault']) . "</td>
                        <td>" . number_format($round['wallets_after']['global_vault']) . "</td>
                        <td class='" . ($round['wallet_changes']['jackpot_wallet'] >= 0 ? 'text-success' : 'text-danger') . "'>" . ($round['wallet_changes']['jackpot_wallet'] >= 0 ? '+' : '') . number_format($round['wallet_changes']['jackpot_wallet']) . "</td>
                        <td>" . number_format($round['wallets_after']['jackpot_wallet']) . "</td>
                        <td class='" . ($round['wallet_changes']['medium_wallet'] >= 0 ? 'text-success' : 'text-danger') . "'>" . ($round['wallet_changes']['medium_wallet'] >= 0 ? '+' : '') . number_format($round['wallet_changes']['medium_wallet']) . "</td>
                        <td>" . number_format($round['wallets_after']['medium_wallet']) . "</td>
                        <td>" . number_format($round['total_app_profit']) . "</td>
                      </tr>";
        }
        
        $html .= "</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    <script>
        document.getElementById('playerFilter').addEventListener('change', function() {
            const filter = this.value;
            const rows = document.querySelectorAll('[data-filterable-row]');
            
            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    row.style.display = row.getAttribute('data-player') === filter ? '' : 'none';
                }
            });
        });
    </script>
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
        $publicPath = public_path("detailed_fairluck_report.html");
        copy($fullPath, $publicPath);
        
        $this->info("📄 تقرير HTML مفصل: {$publicPath}");
    }

    private function displayResults()
    {
        $this->info("\n🎯 ملخص النتائج:");
        $this->info("إجمالي الأدوار: {$this->totalRounds}");
        
        $summaryData = [];
        $totalAttempts = 0;
        $totalWins = 0;
        $totalBets = 0;
        $totalWinnings = 0;
        
        // تجنب تكرار المستخدمين - استخدم مصفوفة فريدة
        $uniqueUsers = [];
        foreach ($this->users as $userData) {
            $userId = $userData['user']->id;
            if (!isset($uniqueUsers[$userId])) {
                $uniqueUsers[$userId] = $userData;
            }
        }
        
        foreach ($uniqueUsers as $userData) {
            $rtp = $userData['total_bet'] > 0 ? ($userData['total_win'] / $userData['total_bet']) * 100 : 0;
            
            $summaryData[] = [
                'المستخدم' => $userData['letter'],
                'المحاولات' => number_format($userData['attempts']),
                'المكاسب' => number_format($userData['wins']),
                'RTP' => number_format($rtp, 1) . '%',
                'الرصيد النهائي' => number_format($userData['final_balance']),
                'الدور النهائي' => $userData['finished_round'],
                'أعلى مضاعف' => $userData['max_multiplier'] . 'x'
            ];
            
            $totalAttempts += $userData['attempts'];
            $totalWins += $userData['wins'];
            $totalBets += $userData['total_bet'];
            $totalWinnings += $userData['total_win'];
        }
        
        $this->table(array_keys($summaryData[0]), array_map('array_values', $summaryData));
        
        $overallRTP = $totalBets > 0 ? ($totalWinnings / $totalBets) * 100 : 0;
        $this->info("📊 RTP الإجمالي: " . number_format($overallRTP, 2) . '%');
        $this->info("📊 صافي ربح التطبيق: " . number_format($this->totalAppProfit));
    }
}