<?php

namespace App\Console\Commands;

use App\Models\FairLuckWallet;
use App\Models\User;
use App\Models\Gift;
use App\Services\FairLuck\FairLuckService3;
use App\Services\FairLuck\DeviationCalculator;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateFairLuckReport extends Command
{
    protected $signature = 'fairluck:generate-report 
                            {user_id=3 : معرف المستخدم}
                            {gift_id=383 : معرف الهدية} 
                            {bet_amount=100 : مبلغ الرهان}
                            {--runs=10 : عدد مرات المحاكاة}
                            {--filename=fairluck_report : اسم الملف}';

    protected $description = 'توليد تقرير HTML شامل لمحاكاة نظام FairLuck';

    private array $results = [];
    private array $initialState = [];

    public function handle()
    {
        $userId = $this->argument('user_id');
        $giftId = $this->argument('gift_id');
        $betAmount = (float) $this->argument('bet_amount');
        $runs = (int) $this->option('runs');
        $filename = $this->option('filename');

        $this->info("🎯 بدء توليد تقرير HTML للمحاكاة");
        
        $user = User::find($userId);
        $gift = Gift::find($giftId);

        if (!$user || !$gift) {
            $this->error('❌ المستخدم أو الهدية غير موجودة');
            return;
        }

        // حفظ الحالة الأولية
        $this->captureInitialState($user, $gift, $betAmount);
        
        // تشغيل المحاكاة
        $this->runSimulation($user, $gift, $betAmount, $runs);
        
        // توليد التقرير
        $htmlContent = $this->generateHtmlReport();
        
        // حفظ الملف
        $filename = "{$filename}_" . date('Y_m_d_H_i_s') . ".html";
        $fullPath = storage_path("app/public/reports/{$filename}");
        
        // إنشاء المجلد إذا لم يكن موجود
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // كتابة الملف
        file_put_contents($fullPath, $htmlContent);
        
        $this->info("✅ تم توليد التقرير بنجاح!");
        $this->line("📄 مسار الملف: {$fullPath}");
        $this->line("🌐 يمكنك فتحه في المتصفح");
    }

    private function captureInitialState($user, $gift, $betAmount)
    {
        $profileManager = app(ProfileManager::class);
        $profile = $profileManager->getProfile($user->id);
        $balances = FairLuckWallet::getAllBalances();
        $rates = DeviationCalculator::getDeductionRates();
        
        $calculator = new DeviationCalculator();
        $currentDeviation = $calculator->calculate(
            $profile->total_bets,
            $profile->total_profit,
            $profile->medium_wallet_wins ?? 0,
            $profile->jackpot_wallet_wins ?? 0
        );

        $this->initialState = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'balance' => $user->di
            ],
            'gift' => [
                'id' => $gift->id,
                'name' => $gift->name
            ],
            'bet_amount' => $betAmount,
            'profile' => [
                'total_bets' => $profile->total_bets,
                'total_profit' => $profile->total_profit,
                'medium_wallet_wins' => $profile->medium_wallet_wins ?? 0,
                'jackpot_wallet_wins' => $profile->jackpot_wallet_wins ?? 0,
                'current_deviation' => $currentDeviation
            ],
            'wallets' => $balances,
            'rates' => $rates,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    private function runSimulation($user, $gift, $betAmount, $runs)
    {
        $this->info("🎲 تشغيل {$runs} محاكاة...");
        $fairLuckService = app(FairLuckService3::class);
        
        for ($i = 1; $i <= $runs; $i++) {
            $this->line("المحاولة #{$i}");
            
            try {
                // الحالة قبل الرهان
                $balancesBefore = FairLuckWallet::getAllBalances();
                $profileManager = app(ProfileManager::class);
                $profileBefore = $profileManager->getProfile($user->id);
                
                // تنفيذ الرهان
                $result = $fairLuckService->processBet($user, $gift, $betAmount);
                
                // الحالة بعد الرهان
                $balancesAfter = FairLuckWallet::getAllBalances();
                $user->refresh();
                $profileAfter = $profileManager->getProfile($user->id);
                
                // حساب التغييرات
                $changes = [
                    'wallets' => [
                        'global_vault' => $balancesAfter['global_vault'] - $balancesBefore['global_vault'],
                        'jackpot_wallet' => $balancesAfter['jackpot_wallet'] - $balancesBefore['jackpot_wallet'],
                        'medium_wallet' => $balancesAfter['medium_wallet'] - $balancesBefore['medium_wallet']
                    ],
                    'profile' => [
                        'total_bets' => $profileAfter->total_bets - $profileBefore->total_bets,
                        'total_profit' => $profileAfter->total_profit - $profileBefore->total_profit,
                        'medium_wins' => ($profileAfter->medium_wallet_wins ?? 0) - ($profileBefore->medium_wallet_wins ?? 0),
                        'jackpot_wins' => ($profileAfter->jackpot_wallet_wins ?? 0) - ($profileBefore->jackpot_wallet_wins ?? 0)
                    ]
                ];
                
                // حفظ النتيجة
                $this->results[] = [
                    'run_number' => $i,
                    'timestamp' => now()->toDateTimeString(),
                    'before' => [
                        'wallets' => $balancesBefore,
                        'profile' => $profileBefore,
                        'user_balance' => $user->di + ($result->isWinner ? -$result->profitAmount : $result->profitAmount)
                    ],
                    'after' => [
                        'wallets' => $balancesAfter,
                        'profile' => $profileAfter,
                        'user_balance' => $user->di
                    ],
                    'result' => [
                        'is_winner' => $result->isWinner,
                        'multiplier' => $result->multiplier,
                        'profit_amount' => $result->profitAmount,
                        'deviation' => $result->newDeviation,
                        'mood' => $result->mood,
                        'house_cut' => $result->houseCut
                    ],
                    'changes' => $changes,
                    'bet_amount' => $betAmount
                ];
                
            } catch (\Exception $e) {
                $this->error("❌ خطأ في المحاولة #{$i}: " . $e->getMessage());
            }
        }
    }

    private function generateHtmlReport(): string
    {
        $totalWins = count(array_filter($this->results, fn($r) => $r['result']['is_winner']));
        $totalLosses = count($this->results) - $totalWins;
        $winRate = count($this->results) > 0 ? ($totalWins / count($this->results)) * 100 : 0;
        
        $totalProfit = array_sum(array_column(array_column($this->results, 'result'), 'profit_amount'));
        
        $multiplierStats = [];
        foreach ($this->results as $result) {
            if ($result['result']['is_winner']) {
                $mult = $result['result']['multiplier'];
                $multiplierStats[$mult] = ($multiplierStats[$mult] ?? 0) + 1;
            }
        }
        
        $html = '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير محاكاة FairLuck - النسخة 3</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { font-size: 1.2em; opacity: 0.9; }
        .content { padding: 30px; }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .stat-card { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 10px; 
            border-left: 4px solid #667eea; 
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { color: #333; margin-bottom: 10px; }
        .stat-card .value { font-size: 1.8em; font-weight: bold; color: #667eea; }
        .section { margin-bottom: 40px; }
        .section h2 { 
            color: #333; 
            margin-bottom: 20px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #667eea; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background: white; 
            border-radius: 10px; 
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        th, td { 
            padding: 12px; 
            text-align: center; 
            border-bottom: 1px solid #eee; 
        }
        th { 
            background: #667eea; 
            color: white; 
            font-weight: 600; 
        }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #e3f2fd; }
        .win { color: #28a745; font-weight: bold; }
        .loss { color: #dc3545; font-weight: bold; }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        .zero { color: #6c757d; }
        .multiplier-high { background: #ffd700; color: #000; }
        .multiplier-medium { background: #ff9800; color: white; }
        .multiplier-low { background: #2196f3; color: white; }
        .chart-container { 
            margin: 20px 0; 
            padding: 20px; 
            background: #f8f9fa; 
            border-radius: 10px; 
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #eee;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 1em;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 تقرير محاكاة نظام FairLuck</h1>
            <p>النسخة الذكية الثالثة - تحليل شامل للأداء</p>
            <p>تاريخ التوليد: ' . $this->initialState['timestamp'] . '</p>
        </div>
        
        <div class="content">
            <!-- الإحصائيات العامة -->
            <div class="section">
                <h2>📊 الإحصائيات العامة</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>إجمالي المحاولات</h3>
                        <div class="value">' . count($this->results) . '</div>
                    </div>
                    <div class="stat-card">
                        <h3>المكاسب</h3>
                        <div class="value win">' . $totalWins . '</div>
                    </div>
                    <div class="stat-card">
                        <h3>الخسائر</h3>
                        <div class="value loss">' . $totalLosses . '</div>
                    </div>
                    <div class="stat-card">
                        <h3>نسبة الفوز</h3>
                        <div class="value">' . number_format($winRate, 1) . '%</div>
                    </div>
                    <div class="stat-card">
                        <h3>إجمالي الربح/الخسارة</h3>
                        <div class="value ' . ($totalProfit >= 0 ? 'positive' : 'negative') . '">' . number_format($totalProfit) . '</div>
                    </div>
                    <div class="stat-card">
                        <h3>متوسط الربح لكل محاولة</h3>
                        <div class="value">' . number_format($totalProfit / count($this->results), 2) . '</div>
                    </div>
                </div>
            </div>

            <!-- الحالة الأولية -->
            <div class="section">
                <h2>🎮 الحالة الأولية</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>المستخدم</h3>
                        <div class="value">' . $this->initialState['user']['name'] . ' (ID: ' . $this->initialState['user']['id'] . ')</div>
                        <p>الرصيد: ' . number_format($this->initialState['user']['balance']) . '</p>
                    </div>
                    <div class="stat-card">
                        <h3>الهدية</h3>
                        <div class="value">' . $this->initialState['gift']['name'] . '</div>
                        <p>ID: ' . $this->initialState['gift']['id'] . '</p>
                    </div>
                    <div class="stat-card">
                        <h3>مبلغ الرهان</h3>
                        <div class="value">' . number_format($this->initialState['bet_amount']) . '</div>
                    </div>
                </div>

                <div class="chart-container">
                    <h3>💰 أرصدة المحافظ الأولية</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>المحفظة</th>
                                <th>الرصيد</th>
                                <th>النسبة</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        $totalWallets = array_sum($this->initialState['wallets']);
        foreach ($this->initialState['wallets'] as $wallet => $balance) {
            $percentage = $totalWallets > 0 ? ($balance / $totalWallets) * 100 : 0;
            $walletName = match($wallet) {
                'global_vault' => 'المحفظة الرئيسية',
                'jackpot_wallet' => 'محفظة الجاكبوت',
                'medium_wallet' => 'المحفظة المتوسطة',
                default => $wallet
            };
            $html .= "<tr>
                        <td>{$walletName}</td>
                        <td>" . number_format($balance) . "</td>
                        <td>" . number_format($percentage, 1) . "%</td>
                      </tr>";
        }
        
        $html .= '</tbody>
                    </table>
                </div>
            </div>

            <!-- تفاصيل المحاكاة -->
            <div class="section">
                <h2>🎲 تفاصيل المحاكاة</h2>
                
                <div class="tabs">
                    <button class="tab active" onclick="showTab(\'results\')">النتائج التفصيلية</button>
                    <button class="tab" onclick="showTab(\'multipliers\')">إحصائيات المضاعفات</button>
                    <button class="tab" onclick="showTab(\'wallets\')">تغييرات المحافظ</button>
                </div>

                <div id="results" class="tab-content active">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الحالة</th>
                                <th>المضاعف</th>
                                <th>الربح/الخسارة</th>
                                <th>معامل الانحراف</th>
                                <th>مزاج النظام</th>
                                <th>خصم البيت</th>
                                <th>الوقت</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        foreach ($this->results as $result) {
            $status = $result['result']['is_winner'] ? '🏆 فوز' : '💔 خسارة';
            $statusClass = $result['result']['is_winner'] ? 'win' : 'loss';
            $multiplier = $result['result']['is_winner'] ? $result['result']['multiplier'] . 'x' : '-';
            $multiplierClass = '';
            
            if ($result['result']['is_winner']) {
                $mult = $result['result']['multiplier'];
                if ($mult >= 250) $multiplierClass = 'multiplier-high';
                elseif ($mult >= 50) $multiplierClass = 'multiplier-medium';
                else $multiplierClass = 'multiplier-low';
            }
            
            $profitClass = $result['result']['profit_amount'] >= 0 ? 'positive' : 'negative';
            $mood = match($result['result']['mood']) {
                'Generous' => '😊 سخي',
                'Recovery' => '😤 استرداد',
                'Stable' => '😐 مستقر',
                default => '🤔 غير محدد'
            };
            
            $html .= "<tr>
                        <td>{$result['run_number']}</td>
                        <td><span class='{$statusClass}'>{$status}</span></td>
                        <td><span class='{$multiplierClass}'>{$multiplier}</span></td>
                        <td><span class='{$profitClass}'>" . number_format($result['result']['profit_amount']) . "</span></td>
                        <td>" . number_format($result['result']['deviation'], 4) . "</td>
                        <td>{$mood}</td>
                        <td>" . number_format($result['result']['house_cut']) . "</td>
                        <td>" . date('H:i:s', strtotime($result['timestamp'])) . "</td>
                      </tr>";
        }
        
        $html .= '</tbody>
                    </table>
                </div>

                <div id="multipliers" class="tab-content">
                    <div class="chart-container">
                        <h3>📈 توزيع المضاعفات</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>المضاعف</th>
                                    <th>عدد المرات</th>
                                    <th>النسبة من الفوز</th>
                                    <th>النسبة الإجمالية</th>
                                </tr>
                            </thead>
                            <tbody>';
        
        ksort($multiplierStats);
        foreach ($multiplierStats as $mult => $count) {
            $winPercentage = $totalWins > 0 ? ($count / $totalWins) * 100 : 0;
            $totalPercentage = count($this->results) > 0 ? ($count / count($this->results)) * 100 : 0;
            
            $html .= "<tr>
                        <td>{$mult}x</td>
                        <td>{$count}</td>
                        <td>" . number_format($winPercentage, 1) . "%</td>
                        <td>" . number_format($totalPercentage, 1) . "%</td>
                      </tr>";
        }
        
        $html .= '</tbody>
                    </table>
                    </div>
                </div>

                <div id="wallets" class="tab-content">
                    <div class="chart-container">
                        <h3>💼 تغييرات المحافظ</h3>';
        
        if (!empty($this->results)) {
            $lastResult = end($this->results);
            $finalWallets = $lastResult['after']['wallets'];
            $totalChanges = [
                'global_vault' => $finalWallets['global_vault'] - $this->initialState['wallets']['global_vault'],
                'jackpot_wallet' => $finalWallets['jackpot_wallet'] - $this->initialState['wallets']['jackpot_wallet'],
                'medium_wallet' => $finalWallets['medium_wallet'] - $this->initialState['wallets']['medium_wallet']
            ];
            
            $html .= '<table>
                        <thead>
                            <tr>
                                <th>المحفظة</th>
                                <th>الرصيد الأولي</th>
                                <th>الرصيد النهائي</th>
                                <th>التغيير</th>
                                <th>نسبة التغيير</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($this->initialState['wallets'] as $wallet => $initialBalance) {
                $finalBalance = $finalWallets[$wallet];
                $change = $totalChanges[$wallet];
                $changePercentage = $initialBalance > 0 ? ($change / $initialBalance) * 100 : 0;
                $changeClass = $change >= 0 ? 'positive' : 'negative';
                
                $walletName = match($wallet) {
                    'global_vault' => 'المحفظة الرئيسية',
                    'jackpot_wallet' => 'محفظة الجاكبوت',
                    'medium_wallet' => 'المحفظة المتوسطة',
                    default => $wallet
                };
                
                $html .= "<tr>
                            <td>{$walletName}</td>
                            <td>" . number_format($initialBalance) . "</td>
                            <td>" . number_format($finalBalance) . "</td>
                            <td><span class='{$changeClass}'>" . ($change >= 0 ? '+' : '') . number_format($change) . "</span></td>
                            <td><span class='{$changeClass}'>" . ($changePercentage >= 0 ? '+' : '') . number_format($changePercentage, 2) . "%</span></td>
                          </tr>";
            }
            
            $html .= '</tbody></table>';
        }
        
        $html .= '</div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>🎯 نظام FairLuck الذكي - النسخة 3</p>
            <p>تم توليد هذا التقرير في: ' . now()->toDateTimeString() . '</p>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // إخفاء جميع المحتويات
            document.querySelectorAll(\'.tab-content\').forEach(content => {
                content.classList.remove(\'active\');
            });
            
            // إلغاء تحديد جميع التبويبات
            document.querySelectorAll(\'.tab\').forEach(tab => {
                tab.classList.remove(\'active\');
            });
            
            // إظهار المحتوى المحدد
            document.getElementById(tabName).classList.add(\'active\');
            
            // تحديد التبويب النشط
            event.target.classList.add(\'active\');
        }
    </script>
</body>
</html>';

        return $html;
    }
}