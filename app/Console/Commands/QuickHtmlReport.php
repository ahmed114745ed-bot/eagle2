<?php

namespace App\Console\Commands;

use App\Models\FairLuckWallet;
use App\Models\User;
use App\Models\Gift;
use App\Services\FairLuck\FairLuckService3;
use App\Services\FairLuck\DeviationCalculator;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Console\Command;

class QuickHtmlReport extends Command
{
    protected $signature = 'fairluck:html-quick 
                            {user_id=3 : معرف المستخدم}
                            {gift_id=383 : معرف الهدية} 
                            {bet_amount=100 : مبلغ الرهان}
                            {--runs=5 : عدد مرات المحاكاة}';

    protected $description = 'توليد تقرير HTML سريع لمحاكاة نظام FairLuck';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $giftId = $this->argument('gift_id');
        $betAmount = (float) $this->argument('bet_amount');
        $runs = (int) $this->option('runs');

        $this->info("🚀 توليد تقرير HTML سريع...");
        
        $user = User::find($userId);
        $gift = Gift::find($giftId);

        if (!$user || !$gift) {
            $this->error('❌ المستخدم أو الهدية غير موجودة');
            return;
        }

        $results = [];
        $fairLuckService = app(FairLuckService3::class);
        
        // الحالة الأولية
        $initialBalances = FairLuckWallet::getAllBalances();
        $profileManager = app(ProfileManager::class);
        $initialProfile = $profileManager->getProfile($user->id);
        
        $calculator = new DeviationCalculator();
        $initialDeviation = $calculator->calculate(
            $initialProfile->total_bets,
            $initialProfile->total_profit,
            $initialProfile->medium_wallet_wins ?? 0,
            $initialProfile->jackpot_wallet_wins ?? 0
        );

        // تشغيل المحاكاة
        for ($i = 1; $i <= $runs; $i++) {
            try {
                $result = $fairLuckService->processBet($user, $gift, $betAmount);
                $user->refresh();
                
                $results[] = [
                    'run' => $i,
                    'winner' => $result->isWinner,
                    'multiplier' => $result->multiplier,
                    'profit' => $result->profitAmount,
                    'deviation' => $result->newDeviation,
                    'mood' => $result->mood
                ];
                
            } catch (\Exception $e) {
                $this->error("❌ خطأ في المحاولة #{$i}: " . $e->getMessage());
            }
        }
        
        // الحالة النهائية
        $finalBalances = FairLuckWallet::getAllBalances();
        $finalProfile = $profileManager->getProfile($user->id);
        
        // إنشاء HTML
        $html = $this->generateQuickHtml($results, $initialBalances, $finalBalances, $initialProfile, $finalProfile, $initialDeviation, $user, $gift, $betAmount);
        
        // حفظ الملف
        $filename = "quick_report_" . date('Y_m_d_H_i_s') . ".html";
        $fullPath = storage_path("app/public/reports/{$filename}");
        
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($fullPath, $html);
        
        // نسخ إلى public
        $publicPath = public_path("fairluck_quick_report.html");
        copy($fullPath, $publicPath);
        
        $this->info("✅ تم توليد التقرير السريع!");
        $this->line("📄 مسار الملف: {$fullPath}");
        $this->line("🌐 متاح أيضاً في: {$publicPath}");
        
        // عرض ملخص سريع
        $totalWins = count(array_filter($results, fn($r) => $r['winner']));
        $totalProfit = array_sum(array_column($results, 'profit'));
        $this->table(
            ['المتغير', 'القيمة'],
            [
                ['عدد المحاولات', count($results)],
                ['عدد المكاسب', $totalWins],
                ['نسبة الفوز', number_format(($totalWins/count($results))*100, 1) . '%'],
                ['إجمالي الربح/الخسارة', number_format($totalProfit)],
                ['الانحراف النهائي', isset($results[count($results)-1]) ? number_format($results[count($results)-1]['deviation'], 4) : 'N/A']
            ]
        );
    }

    private function generateQuickHtml($results, $initialBalances, $finalBalances, $initialProfile, $finalProfile, $initialDeviation, $user, $gift, $betAmount)
    {
        $totalWins = count(array_filter($results, fn($r) => $r['winner']));
        $totalProfit = array_sum(array_column($results, 'profit'));
        $winRate = count($results) > 0 ? ($totalWins / count($results)) * 100 : 0;
        
        $html = '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير سريع - نظام FairLuck</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .summary { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            gap: 15px; 
            padding: 30px;
            background: #f8f9fa;
        }
        .summary-card { 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .summary-card:hover { transform: translateY(-3px); }
        .summary-card h3 { color: #666; font-size: 0.9em; margin-bottom: 8px; }
        .summary-card .value { font-size: 1.8em; font-weight: bold; color: #667eea; }
        .win { color: #28a745 !important; }
        .loss { color: #dc3545 !important; }
        .content { padding: 30px; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        th, td { padding: 12px; text-align: center; }
        th { background: #667eea; color: white; font-weight: 600; }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #e3f2fd; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #333; margin-bottom: 15px; border-bottom: 3px solid #667eea; padding-bottom: 8px; }
        .wallet-change { padding: 20px; background: #f8f9fa; border-radius: 12px; margin: 20px 0; }
        .positive { color: #28a745; font-weight: bold; }
        .negative { color: #dc3545; font-weight: bold; }
        .zero { color: #6c757d; }
        .footer { background: #f1f3f4; padding: 20px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚡ تقرير سريع - نظام FairLuck</h1>
            <p>تحليل سريع للأداء والنتائج</p>
            <p>' . date('Y-m-d H:i:s') . '</p>
        </div>
        
        <div class="summary">
            <div class="summary-card">
                <h3>المحاولات</h3>
                <div class="value">' . count($results) . '</div>
            </div>
            <div class="summary-card">
                <h3>المكاسب</h3>
                <div class="value win">' . $totalWins . '</div>
            </div>
            <div class="summary-card">
                <h3>نسبة الفوز</h3>
                <div class="value">' . number_format($winRate, 1) . '%</div>
            </div>
            <div class="summary-card">
                <h3>صافي الربح</h3>
                <div class="value ' . ($totalProfit >= 0 ? 'win' : 'loss') . '">' . number_format($totalProfit) . '</div>
            </div>
        </div>
        
        <div class="content">
            <div class="section">
                <h2>📋 بيانات التشغيل</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div><strong>المستخدم:</strong> ' . $user->name . ' (ID: ' . $user->id . ')</div>
                    <div><strong>الهدية:</strong> ' . $gift->name . ' (ID: ' . $gift->id . ')</div>
                    <div><strong>مبلغ الرهان:</strong> ' . number_format($betAmount) . '</div>
                    <div><strong>الانحراف الأولي:</strong> ' . number_format($initialDeviation, 4) . '</div>
                </div>
            </div>
            
            <div class="section">
                <h2>🎲 النتائج التفصيلية</h2>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>النتيجة</th>
                            <th>المضاعف</th>
                            <th>الربح/الخسارة</th>
                            <th>معامل الانحراف</th>
                            <th>مزاج النظام</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($results as $result) {
            $status = $result['winner'] ? '🏆 فوز' : '💔 خسارة';
            $statusClass = $result['winner'] ? 'win' : 'loss';
            $multiplier = $result['winner'] ? $result['multiplier'] . 'x' : '-';
            $profitClass = $result['profit'] >= 0 ? 'positive' : 'negative';
            $mood = match($result['mood']) {
                'Generous' => '😊 سخي',
                'Recovery' => '😤 استرداد',
                'Stable' => '😐 مستقر',
                default => '🤔 غير محدد'
            };
            
            $html .= "<tr>
                        <td>{$result['run']}</td>
                        <td><span class='{$statusClass}'>{$status}</span></td>
                        <td>{$multiplier}</td>
                        <td><span class='{$profitClass}'>" . number_format($result['profit']) . "</span></td>
                        <td>" . number_format($result['deviation'], 4) . "</td>
                        <td>{$mood}</td>
                      </tr>";
        }
        
        $html .= '</tbody>
                </table>
            </div>
            
            <div class="section">
                <h2>💰 تغييرات المحافظ</h2>
                <div class="wallet-change">
                    <table>
                        <thead>
                            <tr>
                                <th>المحفظة</th>
                                <th>الرصيد الأولي</th>
                                <th>الرصيد النهائي</th>
                                <th>التغيير</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        $walletNames = [
            'global_vault' => 'المحفظة الرئيسية',
            'jackpot_wallet' => 'محفظة الجاكبوت',
            'medium_wallet' => 'المحفظة المتوسطة'
        ];
        
        foreach ($initialBalances as $wallet => $initialBalance) {
            $finalBalance = $finalBalances[$wallet];
            $change = $finalBalance - $initialBalance;
            $changeClass = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'zero');
            $walletName = $walletNames[$wallet] ?? $wallet;
            
            $html .= "<tr>
                        <td>{$walletName}</td>
                        <td>" . number_format($initialBalance) . "</td>
                        <td>" . number_format($finalBalance) . "</td>
                        <td><span class='{$changeClass}'>" . ($change >= 0 ? '+' : '') . number_format($change) . "</span></td>
                      </tr>";
        }
        
        $html .= '</tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>🎯 نظام FairLuck الذكي - النسخة 3</p>
            <p>تقرير تم توليده في: ' . now()->toDateTimeString() . '</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
}