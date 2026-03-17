<?php

namespace App\Console\Commands;

use App\Models\FairLuckWallet;
use App\Models\User;
use App\Models\Gift;
use App\Services\FairLuck\ProfileManager;
use App\Services\FairLuck\V6\FairLuckServiceV6;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class DetailedMultiUserReportV6 extends Command
{
    protected $signature = 'fairluck:detailed-multi-v6 
                            {gift_id=383 : معرف الهدية} 
                            {bet_amount=100 : مبلغ الرهان}
                            {--users=3 : عدد المستخدمين}
                            {--initial_balance=1000 : الرصيد الابتدائي}
                            {--max_rounds=3000 : الحد الأقصى للأدوار}
                            {--force_completion : إجبار الإكمال حتى انتهاء جميع الأرصدة}
                            {--vault=50000 : رصيد المحفظة الموحدة الابتدائي}';

    protected $description = 'تقرير شامل V6 لمحفظة موحدة مع تتبع الميزانية السالبة والانهيار';

    private array $users = [];
    private array $allRounds = [];
    private int $totalRounds = 0;
    private float $totalAppProfit = 0;
    private float $minVaultBalance = 0;

    public function handle()
    {
        $giftId = $this->argument('gift_id');
        $betAmount = (float) $this->argument('bet_amount');
        $userCount = (int) $this->option('users');
        $initialBalance = (float) $this->option('initial_balance');
        $forceCompletion = $this->option('force_completion');

        $this->info("🎯 تقرير V6 شامل مع {$userCount} مستخدمين");
        $this->info("💰 الرصيد الابتدائي: " . number_format($initialBalance));
        $maxRounds = (int) $this->option('max_rounds');
        $initialVault = (int) $this->option('vault');
        $this->info("⏰ حد أقصى: {$maxRounds} دور | 🏦 رصيد المحفظة الابتدائي: " . number_format($initialVault));
        
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
        // تنظيف المستخدمين القدامى لتجنب تضخم القاعدة والاستهلاك العشوائي للأسماء
        User::where('name', 'like', 'V6_Tester_%')->delete();
        
        $letters = range('A', 'Z');
        for ($i = 0; $i < $userCount; $i++) {
            $letter = $letters[$i] ?? 'X';
            $username = "V6_Tester_{$letter}_" . time() . rand(100, 999);
            
            $user = User::create([
                'name' => $username,
                'email' => strtolower($letter) . '_v6_' . time() . rand(100,999) . '@test.local',
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
        $round = 1;
        $activeUsers = count($this->users);
        
        // تعيين أرصدة المحافظ الثلاثة لـ V6
        $initialVault = (int) $this->option('vault');
        $this->info("🔄 تهيئة محافظ V6 (Global, Medium, Jackpot) إجمالي: " . number_format($initialVault) . "...");
        
        // توزيع الرصيد الابتدائي بنسب PoolManager (65/20/15)
        $v6Wallets = [
            'global_vault' => (int) round($initialVault * 0.65),
            'medium_wallet' => (int) round($initialVault * 0.15),
            'jackpot_wallet' => (int) round($initialVault * 0.20),
        ];

        foreach ($v6Wallets as $type => $balance) {
            FairLuckWallet::updateOrCreate(
                ['wallet_type' => $type],
                ['balance' => $balance, 'last_updated' => now()]
            );
            Redis::set("fairluck:wallet:{$type}", $balance);
        }

        $this->minVaultBalance = $initialVault;

        // تصفير المحفظة الموحدة (V5) لضمان عدم التداخل
        FairLuckWallet::updateOrCreate(
            ['wallet_type' => 'unified_vault'],
            ['balance' => 0, 'last_updated' => now()]
        );
        Redis::set("fairluck:wallet:unified_vault", 0);

        // تأكيد التهيئة
        $poolCheck = app(\App\Services\FairLuck\V6\PoolManager::class)->getTotalBalance();
        $this->info("✅ تم تهيئة المحافظ - إجمالي Pool: " . number_format($poolCheck));
        
        $this->info("🎲 بدء المحاكاة V6 (نظام 3 محافظ)...");
        
        while ($activeUsers > 0) {
            if ($round % 50 == 0) {
                $poolTotal = app(\App\Services\FairLuck\V6\PoolManager::class)->getTotalBalance();
                $this->line("الدور #{$round} - مستخدمين نشطين: {$activeUsers} | إجمالي السيولة: " . number_format($poolTotal));
            }
            
            foreach ($this->users as $index => &$userData) {
                // مطابق لـ LuckyGiftService::sendLuckyGift6
                $unitPrice = $betAmount; // سعر الهدية = ما يدفعه اليوزر
                $appFeeRate = \App\Models\FairLuckSetting::getAppFeeRate();       // 0.10
                $receiverFeeRate = \App\Models\FairLuckSetting::getReceiverFeeRate(); // 0.10
                $ownerFeeRate = \App\Models\FairLuckSetting::getOwnerFeeRate();    // 0.10

                $appFee = $unitPrice * $appFeeRate;
                $receiverFee = $unitPrice * $receiverFeeRate;
                $ownerFee = $unitPrice * $ownerFeeRate;
                $netBetAmount = $unitPrice - $appFee - $receiverFee - $ownerFee;

                if ($userData['user']->di < $unitPrice) {
                    continue;
                }

                try {
                    $poolManager = app(\App\Services\FairLuck\V6\PoolManager::class);
                    $walletsBefore = $poolManager->getTotalBalance();

                    $profileManager = app(ProfileManager::class);
                    $profileBefore = $profileManager->getProfile($userData['user']->id);
                    $fairService = app(FairLuckServiceV6::class);

                    $userBalanceBefore = $userData['user']->di;

                    // خصم سعر الهدية فقط (مثل LuckyGiftService سطر 1162)
                    $userData['user']->di -= $unitPrice;
                    $userData['user']->save();

                    $result = $fairService->processBet(
                        $userData['user'],
                        $gift,
                        $netBetAmount,
                        $unitPrice,
                        null,
                        null,
                        $appFee,
                        $receiverFee,
                        $userBalanceBefore,
                        $userData['user']->di
                    );
                    
                    // إضافة الفوز يدوياً
                    if ($result->isWinner && $result->profitAmount > 0) {
                        $userData['user']->di += $result->profitAmount;
                        $userData['user']->save();
                    }

                    $userData['user']->refresh();
                    $userBalanceAfter = $userData['user']->di;
                    
                    $actualBalanceChange = $userBalanceAfter - $userBalanceBefore;
                    $userData['attempts']++;
                    $userData['total_bet'] += $unitPrice;
                    
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
                    
                    $walletsAfter = $poolManager->getTotalBalance();
                    if ($walletsAfter < $this->minVaultBalance) {
                        $this->minVaultBalance = $walletsAfter;
                    }
                    
                    $this->totalAppProfit += ($unitPrice - ($result->isWinner ? $result->profitAmount : 0));
                    
                    $this->allRounds[] = [
                        'global_round' => $round,
                        'user_round' => $userData['attempts'],
                        'user_letter' => $userData['letter'],
                        'result' => $result,
                        'wallets_before' => $walletsBefore,
                        'wallets_after' => $walletsAfter,
                        'bet_amount' => $betAmount,
                        'user_balance_before' => $userBalanceBefore,
                        'user_balance_after' => $userBalanceAfter,
                        'user_balance_change' => $actualBalanceChange,
                        'total_app_profit' => $this->totalAppProfit
                    ];
                    
                    if ($userData['user']->di < $unitPrice && $userData['finished_round'] == 0) {
                        $userData['finished_round'] = $round;
                        $this->line("💸 انتهى رصيد المستخدم {$userData['letter']} في الدور {$round}");
                    }
                    
                } catch (\Exception $e) {
                    $this->error("خطأ: " . $e->getMessage());
                }
            }
            unset($userData); // break reference from &$userData foreach

            $activeUsers = 0;
            foreach ($this->users as $userData) {
                if ($userData['user']->di >= $betAmount) {
                    $activeUsers++;
                }
            }
            
            $round++;
            if ($activeUsers == 0) break;
            
            $maxRounds = (int) $this->option('max_rounds');
            if ($round > $maxRounds) break;
        }
        
        $this->totalRounds = $round - 1;
    }

    private function generateDetailedHtmlReport($gift, $betAmount, $initialBalance)
    {
        $timestamp = date('Y_m_d_H_i_s');
        $filename = "v6_simulation_report.html";
        
        $totalAttempts = array_sum(array_column($this->users, 'attempts'));
        $totalWins = array_sum(array_column($this->users, 'wins'));
        $totalBets = array_sum(array_column($this->users, 'total_bet'));
        $totalWinnings = array_sum(array_column($this->users, 'total_win'));
        $overallRTP = $totalBets > 0 ? ($totalWinnings / $totalBets) * 100 : 0;
        
        $html = "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>تقرير FairLuck V6 مفصل - متعدد المستخدمين</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background:#f8f9fb; font-family:'Segoe UI', Tahoma, sans-serif; }
        h1, h2 { font-weight:700; }
        .table thead th { white-space:nowrap; }
        .user-card { transition: transform 0.3s; }
        .user-card:hover { transform: translateY(-5px); }
        .winner-row { background-color: #d4edda !important; }
        .loser-row { background-color: #f8d7da !important; }
        .stats-summary { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; }
        .vault-negative { color: #d32f2f; font-weight: bold; }
        .vault-positive { color: #388e3c; }
    </style>
</head>
<body>
    <div class='container py-5'>
        <div class='stats-summary p-4 rounded mb-5 shadow'>
            <h1 class='text-center mb-4'>تقرير محاكاة FairLuck V6</h1>
            <p class='text-center mb-4'>الهدية: {$gift->name} | مبلغ الرهان: " . number_format($betAmount) . " | رصيد ابتدائي: " . number_format($initialBalance) . " | إجمالي الأدوار: {$this->totalRounds}</p>
            <div class='row text-center'>
                <div class='col-md-4'>
                    <h6>أقل رصيد للمحفظة</h6>
                    <h3 class='" . ($this->minVaultBalance < 0 ? 'text-warning' : '') . "'>" . number_format($this->minVaultBalance) . "</h3>
                </div>
                <div class='col-md-4'>
                    <h6>صافي ربح النظام</h6>
                    <h3>" . number_format($this->totalAppProfit) . "</h3>
                </div>
                <div class='col-md-4'>
                    <h6>RTP الإجمالي</h6>
                    <h3>" . number_format($overallRTP, 2) . "%</h3>
                </div>
            </div>
        </div>

        <!-- بطاقات المستخدمين -->
        <div class='row mb-4'>";
        
        foreach ($this->users as $userData) {
            $rtp = $userData['total_bet'] > 0 ? ($userData['total_win'] / $userData['total_bet']) * 100 : 0;
            
            $html .= "<div class='col-md-4 mb-4'>
                <div class='card shadow-sm h-100 user-card'>
                    <div class='card-body'>
                        <h4 class='card-title'>المستخدم {$userData['letter']}</h4>
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

        <!-- الجدول التفصيلي -->
        <div class='card shadow-sm'>
            <div class='card-body'>
                <div class='row g-3 align-items-end mb-4'>
                    <div class='col-12 col-lg-8'>
                        <h2 class='h4 mb-1'>التفاصيل الكاملة للأدوار (نظام V6)</h2>
                        <p class='text-muted mb-0'>تتبع السيولة عبر المحافظ الثلاث (Global, Medium, Jackpot)</p>
                    </div>
                    <div class='col-12 col-lg-4'>
                        <select id='playerFilter' class='form-select'>
                            <option value='all'>جميع المستخدمين</option>";
        foreach ($this->users as $userData) {
            $html .= "<option value='{$userData['letter']}'>المستخدم {$userData['letter']}</option>";
        }
        $html .= "</select>
                    </div>
                </div>
                
                <div class='table-responsive'>
                    <table class='table table-striped align-middle small'>
                        <thead class='table-dark'>
                            <tr>
                                <th>#</th>
                                <th>المستخدم</th>
                                <th>النتيجة</th>
                                <th>المضاعف</th>
                                <th>رصيد اللاعب (قبل/بعد)</th>
                                <th>المحفظة (قبل)</th>
                                <th>التغيير</th>
                                <th>المحفظة (بعد)</th>
                                <th>تراكمي الربح</th>
                            </tr>
                        </thead>
                        <tbody>";
        
        foreach ($this->allRounds as $index => $roundData) {
            $isWinner = $roundData['result']->isWinner;
            $rowClass = $isWinner ? 'winner-row' : 'loser-row';
            $vaultChange = $roundData['wallets_after'] - $roundData['wallets_before'];
            
            $html .= "<tr class='{$rowClass}' data-player='{$roundData['user_letter']}'>
                        <td>{$roundData['global_round']}</td>
                        <td>{$roundData['user_letter']}</td>
                        <td>" . ($isWinner ? '✅ فوز' : '❌ خسارة') . "</td>
                        <td>" . ($isWinner ? $roundData['result']->multiplier . 'x' : '-') . "</td>
                        <td>" . number_format($roundData['user_balance_before']) . " ➔ " . number_format($roundData['user_balance_after']) . "</td>
                        <td>" . number_format($roundData['wallets_before']) . "</td>
                        <td class='" . ($vaultChange >= 0 ? 'vault-positive' : 'vault-negative') . "'>" . ($vaultChange >= 0 ? '+' : '') . number_format($vaultChange) . "</td>
                        <td class='" . ($roundData['wallets_after'] < 0 ? 'vault-negative' : '') . "'>" . number_format($roundData['wallets_after']) . "</td>
                        <td>" . number_format($roundData['total_app_profit']) . "</td>
                      </tr>";
        }
        
        $html .= "</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('playerFilter').addEventListener('change', function() {
            const filter = this.value;
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = (filter === 'all' || row.dataset.player === filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>";
        
        $publicPath = public_path($filename);
        file_put_contents($publicPath, $html);
        $this->info("📄 التقرير متوفر في: {$publicPath}");
    }

    private function displayResults()
    {
        $this->info("\n🎯 ملخص النتائج V6:");
        $this->info("إجمالي الأدوار: {$this->totalRounds}");
        $this->info("أقل فجوة سيولة: " . number_format($this->minVaultBalance));
        
        $summaryData = [];
        foreach ($this->users as $userData) {
            $rtp = $userData['total_bet'] > 0 ? ($userData['total_win'] / $userData['total_bet']) * 100 : 0;
            $summaryData[] = [
                'المستخدم' => $userData['letter'],
                'المحاولات' => $userData['attempts'],
                'المكاسب' => $userData['wins'],
                'RTP' => number_format($rtp, 1) . '%',
                'الرصيد النهائي' => number_format($userData['final_balance']),
                'أعلى مضاعف' => $userData['max_multiplier'] . 'x'
            ];
        }
        
        $this->table(array_keys($summaryData[0]), array_values($summaryData));
        $this->info("📊 صافي ربح التطبيق: " . number_format($this->totalAppProfit));
    }
}
