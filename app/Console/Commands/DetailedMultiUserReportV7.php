<?php



namespace App\Console\Commands;


use App\Models\CoreWallet;
use App\Models\FairLuckWallet;
use App\Models\User;
use App\Models\Gift;
use App\Services\FairLuck\V7\FairLuckServiceV7;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Console\Command;

class DetailedMultiUserReportV7 extends Command
{
    protected $signature = 'fairluck:detailed-multi-v7 
                            {gift_id=383 : معرف الهدية} 
                            {bet_amount=100 : مبلغ الرهان}
                            {--users=3 : عدد المستخدمين}
                            {--initial_balance=1000 : الرصيد الابتدائي}
                            {--max_rounds=3000 : الحد الأقصى للأدوار}
                            {--force_completion : إجبار الإكمال حتى انتهاء جميع الأرصدة}
                            {--unit_price=1 : سعر الوحدة}
                            {--vault=500000 : رصيد المحفظة الموحدة الابتدائي}';

    protected $description = 'تقرير شامل متعدد المستخدمين مع تفاصيل كاملة للمحافظ وفلاتر - الإصدار السابع V7';

    private array $users = [];
    private array $allRounds = [];
    private array $walletHistory = [];
    private int $totalRounds = 0;
    private float $totalAppProfit = 0;
    private int $initialAppWalletBalance = 0;
    private int $initialVaultBalance = 0;

    public function handle()
    {
        $giftId = $this->argument('gift_id');
        $betAmount = (float) $this->argument('bet_amount');
        $userCount = (int) $this->option('users');
        $initialBalance = (float) $this->option('initial_balance');
        $unitPrice = (float) $this->option('unit_price');
        $forceCompletion = $this->option('force_completion');

        $this->info("🎯 تقرير شامل مع {$userCount} مستخدمين - الإصدار السابع V7");
        $this->info("💰 الرصيد الابتدائي: " . number_format($initialBalance) . " | مبلغ الرهان: " . number_format($betAmount));
        $maxRounds = (int) $this->option('max_rounds');
        $initialVault = (int) $this->option('vault');
        $this->info("⏰ حد أقصى: {$maxRounds} دور | 🏦 رصيد المحفظة الابتدائي: " . number_format($initialVault));
        
        // Seed V7 settings before running
        $this->seedV7Settings();
        
        // Display current settings
        $this->displaySettings();
        
        $gift = Gift::find($giftId);
        if (!$gift) {
            $this->error('❌ الهدية غير موجودة');
            return;
        }

        // إنشاء المستخدمين
        $this->createTestUsers($userCount, $initialBalance);
        
        // تشغيل المحاكاة الشاملة
        $this->runDetailedSimulation($gift, $betAmount, $unitPrice, $forceCompletion);
        
        // توليد تقرير HTML مفصل
        $this->generateDetailedHtmlReport($gift, $betAmount, $initialBalance);
        
        // عرض النتائج
        $this->displayResults();
    }

    /**
     * Seed V7 settings to ensure simulation uses correct values
     */
    private function seedV7Settings(): void
    {
        $this->info("🔧 تهيئة إعدادات V7...");
        
        $defaultSettings = [
            // Core RTP Settings
            // 123.75% على net_bet = 99% على betAmount الكامل (fees=20%)
            // Pool تخسر ببطء وتُعوَّض بإيداعات دورية
            'V7_target_rtp' => 1.2375,
            'V7_max_probability_cap' => 0.50,  // سقف الاحتمالية
            'V7_boost_scaling' => 0.03,         // تقليل التعزيز عند الخسارة
            'V7_reduce_scaling' => 0.08,        // رفع التخفيض عند الفوز الزائد
            'V7_chaos_factor_min' => 0.95,
            'V7_chaos_factor_max' => 1.05,
            
            // Multiplier Weights - 250x و500x مخفضة قليلاً لتحقيق RTP=99%
            'V7_multiplier_weights' => json_encode([
                    5    => 9000,  // 5x يهيمن → avgMult ≈ 8x → baseProb ≈ 12% → winRate ~12%
                    10   => 1000,  // 10x شائع (~9% من الفوز)
                    20   => 300,   // 20x متوسط (~2.7%)
                    50   => 100,   // 50x أقل شيوعاً (~0.9%)
                    100  => 50,    // 100x نادر (~0.45%)
                    250  => 120,   // 250x قابل للظهور - مخفض قليلاً (~1.1% من الفوز)
                    500  => 40,    // 500x قابل للظهور - مخفض قليلاً (~0.37% من الفوز)
                    1000 => 10,    // 1000x جاكبوت نادر (~0.09%)
            ]),

            // New Player Settings - تعطيل boost المستخدم الجديد لتحقيق RTP دقيق
            'V7_new_player_bets' => 0,     // تعطيل (0 = disabled)
            'V7_new_player_boost' => 1.0,  // لا boost

            // Low Balance Protection
            'V7_low_balance_threshold' => 10,
            'V7_low_balance_min_prob' => 0.30,  // رفع من 0.18 إلى 0.30

            // Wallet Protection (USD)
            'coin_to_usd_rate' => 0.01,
            'wallet_healthy_usd' => 1000,
            'wallet_warning_usd' => 500,
            'wallet_critical_usd' => 200,
            'wallet_max_negative_usd' => 500,  // رفع من 300 إلى 500

            // Wallet-based max multipliers
            'V7_wallet_healthy_max_mult' => 1000,
            'V7_wallet_moderate_max_mult' => 250,  // رفع من 100 إلى 250
            'V7_wallet_low_max_mult' => 100,        // رفع من 50 إلى 100
            'V7_wallet_critical_max_mult' => 50,

            // Min probability when wallet low (60% floor)
            'V7_min_prob_when_low' => 0.60,

            // Loss Streak Protection - Force win after max streak
            'V7_max_loss_streak' => 15,            // تقليل من 20 إلى 15
            'V7_loss_streak_forced_mult' => 5,     // 5x بدلاً من 10x - يتوافق مع الأكثر ظهوراً

            // Cooldown & Safety
            'fairluck_jackpot_cooldown_bets' => 0,
            'V7_min_bets_100x' => 10,   // تقليل من 30 إلى 10
            'V7_min_bets_500x' => 30,   // تقليل من 100 إلى 30
            'V7_max_single_win_pct' => 0.15,  // رفع من 0.10 إلى 0.15

            // Legacy settings
            'global_vault_negative_limit' => 50000,  // رفع من 30000 إلى 50000
            'fair_luck_owner_fee_rate' => 0.10,
        ];
        
        foreach ($defaultSettings as $key => $value) {
            \App\Models\FairLuckSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'description' => 'V7 Default']
            );
        }
        
        // Clear cache to ensure fresh settings
        \Illuminate\Support\Facades\Cache::forget('fair_luck:settings');
        
        $this->info("✅ تم تهيئة " . count($defaultSettings) . " إعداد V7");
    }

    /**
     * Display current V7 settings being used
     */
    private function displaySettings(): void
    {
        $this->info("");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📋 إعدادات V7 المستخدمة في المحاكاة:");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        $settings = [
            ['الإعداد', 'القيمة'],
            ['Target RTP (المستهدف)', (\App\Models\FairLuckSetting::getTargetRTP() * 100) . '%'],
            ['Max Probability Cap', (\App\Models\FairLuckSetting::getByKey('V7_max_probability_cap', 0.50) * 100) . '%'],
            ['Jackpot Cooldown', \App\Models\FairLuckSetting::getByKey('fairluck_jackpot_cooldown_bets', 0) . ' bets (0 = disabled)'],
            ['Min Prob When Low', (\App\Models\FairLuckSetting::getMinProbabilityWhenLow() * 100) . '%'],
            ['Max Single Win %', (\App\Models\FairLuckSetting::getMaxSingleWinPercentage() * 100) . '%'],
            ['Max Loss Streak', \App\Models\FairLuckSetting::getMaxLossStreak()],
            ['Forced Win Mult', \App\Models\FairLuckSetting::getLossStreakForcedMultiplier() . 'x'],
            ['Coin to USD Rate', '$' . \App\Models\FairLuckSetting::getCoinToUsdRate()],
            ['Wallet Healthy (USD)', '$' . \App\Models\FairLuckSetting::getHealthyWalletUsd()],
            ['Wallet Warning (USD)', '$' . \App\Models\FairLuckSetting::getWarningWalletUsd()],
            ['Wallet Critical (USD)', '$' . \App\Models\FairLuckSetting::getCriticalWalletUsd()],
        ];
        
        $this->table(['الإعداد', 'القيمة'], array_slice($settings, 1));
        
        // Show multiplier weights
        $weights = \App\Models\FairLuckSetting::getMultiplierWeights();
        $this->info("📊 أوزان المضاعفات:");
        $weightRows = [];
        foreach ($weights as $mult => $weight) {
            $weightRows[] = [$mult . 'x', $weight];
        }
        $this->table(['المضاعف', 'الوزن'], $weightRows);
        
        // V7: محفظة واحدة فقط
        $this->info("💰 المحفظة: محفظة واحدة موحدة (Global Vault) - 100% من الرهانات");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("");
    }

    private function createTestUsers($userCount, $initialBalance)
    {
        for ($i = 1; $i <= $userCount; $i++) {
            $letter = chr(64 + $i); // A, B, C, etc.
            $username = "DetailedUserV7_{$letter}_" . time() . rand(100, 999);
            
            $user = User::create([
                'name' => $username,
                'email' => strtolower($letter) . '_detail_v7_' . time() . rand(100,999) . '@test.local',
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
                'app_cut' => 0,
                // New tracking fields
                'multiplier_counts' => [5 => 0, 10 => 0, 20 => 0, 50 => 0, 100 => 0, 250 => 0, 500 => 0, 1000 => 0],
                'current_loss_streak' => 0,
                'max_loss_streak' => 0,
                'current_win_streak' => 0,
                'max_win_streak' => 0,
            ];
        }
    }

    private function runDetailedSimulation($gift, $betAmount, $unitPrice, $forceCompletion)
    {
        $round = 1;
        $activeUsers = count($this->users);
        
        // تعيين رصيد المحفظة الموحدة (global_vault) - V7: محفظة واحدة فقط
        $initialVault = (int) $this->option('vault');
        $this->info("🔄 تهيئة المحفظة الموحدة V7 برصيد " . number_format($initialVault) . "...");
        
        // V7: محفظة واحدة فقط - global_vault
        FairLuckWallet::where('wallet_type', FairLuckWallet::TYPE_GLOBAL_VAULT)->update([
            'balance' => $initialVault,
            'last_updated' => now()
        ]);
        \Illuminate\Support\Facades\Redis::set('fairluck:wallet:' . FairLuckWallet::TYPE_GLOBAL_VAULT, $initialVault);
        
        // حفظ الأرصدة الابتدائية لمحفظة اللعب ومحفظة التطبيق
        $this->initialVaultBalance = $initialVault;
        $appWallet = CoreWallet::where('name', 'app_wallet')->first();
        $this->initialAppWalletBalance = $appWallet ? (int) $appWallet->coins : 0;
        
        // حفظ الحالة الأولية للمحافظ
        $this->walletHistory[0] = $this->getWalletBalances();
        $this->info("✅ تم تهيئة المحفظة الموحدة V7");
        $this->info("📊 رصيد محفظة اللعب الابتدائي: " . number_format($this->initialVaultBalance));
        $this->info("📊 رصيد محفظة التطبيق الابتدائي: " . number_format($this->initialAppWalletBalance));
        
        $this->info("🎲 بدء المحاكاة...");
        
        while ($activeUsers > 0) {
            // عرض التقدم كل 50 دور
            if ($round % 50 == 0) {
                $this->line("الدور #{$round} - مستخدمين نشطين: {$activeUsers}");
            }
            
            foreach ($this->users as $index => &$userData) {
                // تحديث الرصيد من قاعدة البيانات قبل الفحص
                $userData['user']->refresh();
                
                if ($userData['user']->di < $betAmount) {
                    continue;
                }
                
                try {
                    // الحالة قبل الرهان
                    $walletsBefore = $this->getWalletBalances();
                    $profileManager = app(ProfileManager::class);
                    $profileBefore = $profileManager->getProfile($userData['user']->id);
                    
                    $fairLuckService = app(FairLuckServiceV7::class);
                    
                    $deviationBefore = (float) $profileBefore->current_deviation;
                    
                    // تنفيذ الرهان - V7
                    $senderBalanceBefore = $userData['user']->di;
                    $senderBalanceAfter = $senderBalanceBefore - $betAmount;
                    
                    $result = $fairLuckService->processBet(
                        $userData['user'], 
                        $gift, 
                        $betAmount,
                        $unitPrice,
                        null, // roomId
                        null, // receiverId
                        0, // appFee
                        0, // receiverFee
                        $senderBalanceBefore, 
                        $senderBalanceAfter,
                        $userData['current_loss_streak'] // Pass loss streak for protection
                    );
                    
                    $userBalanceBefore = $userData['user']->di;
                    // FIX: On win, deduct bet AND add payout. On loss, deduct bet only.
                    // profitAmount from service = payout (multiplier * betAmount), e.g. 5x * 100 = 500
                    if ($result->isWinner && $result->profitAmount > 0) {
                        $newBalance = $userBalanceBefore - $betAmount + $result->profitAmount;
                    } else {
                        $newBalance = $userBalanceBefore - $betAmount;
                    }
                    
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
                    
                    if ($result->isWinner && $result->profitAmount > 0) {
                        $userData['wins']++;
                        $userData['total_win'] += $result->profitAmount;
                        if ($result->multiplier > $userData['max_multiplier']) {
                            $userData['max_multiplier'] = $result->multiplier;
                        }
                        if ($result->multiplier >= 250) {
                            $userData['high_multipliers']++;
                        }
                        // Track multiplier distribution
                        if (isset($userData['multiplier_counts'][$result->multiplier])) {
                            $userData['multiplier_counts'][$result->multiplier]++;
                        }
                        // Track win streak
                        $userData['current_win_streak']++;
                        $userData['current_loss_streak'] = 0;
                        if ($userData['current_win_streak'] > $userData['max_win_streak']) {
                            $userData['max_win_streak'] = $userData['current_win_streak'];
                        }
                    } else {
                        // Track loss streak
                        $userData['current_loss_streak']++;
                        $userData['current_win_streak'] = 0;
                        if ($userData['current_loss_streak'] > $userData['max_loss_streak']) {
                            $userData['max_loss_streak'] = $userData['current_loss_streak'];
                        }
                    }
                    
                    $userData['final_balance'] = $userBalanceAfter;
                    $userData['net_result'] = $userData['total_win'] - $userData['total_bet'];
                    $userData['app_cut'] += 0; // V7 doesn't use house cut in the same way
                    
                    // الحالة بعد الرهان
                    $walletsAfter = $this->getWalletBalances();
                    
                    // حساب تغييرات المحافظ
                    $walletChanges = [
                        'global_vault' => ($walletsAfter['global_vault'] ?? 0) - ($walletsBefore['global_vault'] ?? 0),
                        'jackpot_wallet' => ($walletsAfter['jackpot_wallet'] ?? 0) - ($walletsBefore['jackpot_wallet'] ?? 0),
                        'medium_wallet' => ($walletsAfter['medium_wallet'] ?? 0) - ($walletsBefore['medium_wallet'] ?? 0),
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
                        'deviation_after' => $result->newDeviation ?? 0,
                        'wallets_before' => $walletsBefore,
                        'wallets_after' => $walletsAfter,
                        'wallet_changes' => $walletChanges,
                        'bet_amount' => $betAmount,
                        'user_balance_before' => $userBalanceBefore,
                        'user_balance_after' => $userBalanceAfter,
                        'user_balance_change' => $actualBalanceChange,
                        'user_balance' => $userBalanceAfter,
                        'total_app_profit' => $this->totalAppProfit,
                        'total_player_winnings' => array_sum(array_column($this->users, 'total_win')),
                        'pool_health' => $result->pool_health ?? []
                    ];
                    
                    if ($userData['user']->di < $betAmount && $userData['finished_round'] == 0) {
                        $userData['finished_round'] = $round;
                        $this->line("💸 انتهى رصيد المستخدم {$userData['letter']} في الدور {$round}");
                    }
                    
                } catch (\Exception $e) {
                    $this->error("خطأ للمستخدم {$userData['letter']}: " . $e->getMessage());
                }
            }
            // إلغاء المرجع لتجنب مشكلة PHP الشهيرة مع foreach بالمرجع
            unset($userData);
            
            // حفظ حالة المحافظ بعد كل دور
            $this->walletHistory[$round] = $this->getWalletBalances();
            
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

    private function getWalletBalances(): array
    {
        // V7: محفظة واحدة فقط - global_vault
        $balance = FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
        return [
            'global_vault' => $balance,
            'jackpot_wallet' => 0,
            'medium_wallet' => 0,
        ];
    }

    private function generateDetailedHtmlReport($gift, $betAmount, $initialBalance)
    {
        $timestamp = date('Y_m_d_H_i_s');
        $filename = "detailed_fairluck_report_v7_{$timestamp}.html";
        
        $totalAttempts = array_sum(array_column($this->users, 'attempts'));
        $totalWins = array_sum(array_column($this->users, 'wins'));
        $totalBets = array_sum(array_column($this->users, 'total_bet'));
        $totalWinnings = array_sum(array_column($this->users, 'total_win'));
        $overallRTP = $totalBets > 0 ? ($totalWinnings / $totalBets) * 100 : 0;

        // حساب الأرصدة النهائية لمحفظة اللعب ومحفظة التطبيق
        $finalVaultBalance = FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
        $finalAppWallet = CoreWallet::where('name', 'app_wallet')->first();
        $finalAppWalletBalance = $finalAppWallet ? (int) $finalAppWallet->coins : 0;
        $vaultChange = $finalVaultBalance - $this->initialVaultBalance;
        $appWalletChange = $finalAppWalletBalance - $this->initialAppWalletBalance;
        $vaultChangeSign = $vaultChange >= 0 ? '+' : '';
        $appWalletChangeSign = $appWalletChange >= 0 ? '+' : '';
        
        $vaultChangeColor = $vaultChange >= 0 ? '#28a745' : '#dc3545';
        $appWalletChangeColor = $appWalletChange >= 0 ? '#28a745' : '#dc3545';

        $html = "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>تقرير FairLuck مفصل V7 - متعدد المستخدمين</title>
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
        .v7-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .wallet-card { border-radius: 12px; padding: 20px; color: white; }
        .wallet-game { background: linear-gradient(135deg, #1a73e8, #0d47a1); }
        .wallet-app  { background: linear-gradient(135deg, #e67e22, #c0392b); }
        .wallet-label { font-size: 0.85rem; opacity: 0.85; }
        .wallet-value { font-size: 1.5rem; font-weight: 700; }
        .wallet-change { font-size: 1.1rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class='container py-5'>
        <div class='stats-summary p-4 rounded mb-4'>
            <h1 class='text-center mb-4'>تقرير FairLuck مفصل - متعدد المستخدمين <span class='badge v7-badge'>V7</span></h1>
            <p class='text-center mb-4'>الهدية: {$gift->name} | مبلغ الرهان: " . number_format($betAmount) . " | الرصيد الابتدائي: " . number_format($initialBalance) . " | إجمالي الأدوار: {$this->totalRounds}</p>
            <p class='text-center mb-2'><small>ملاحظة: V7 - محفظة واحدة موحدة (Global Vault) - جميع الرهانات والمدفوعات من محفظة واحدة</small></p>
        </div>

        <!-- بطاقات المحافظ قبل وبعد -->
        <div class='row mb-4 g-3'>
            <div class='col-md-6'>
                <div class='wallet-card wallet-game'>
                    <div class='wallet-label'>🎮 محفظة اللعب (Global Vault)</div>
                    <div class='row mt-2'>
                        <div class='col-4 text-center'>
                            <div class='wallet-label'>قبل</div>
                            <div class='wallet-value'>" . number_format($this->initialVaultBalance) . "</div>
                        </div>
                        <div class='col-4 text-center'>
                            <div class='wallet-label'>بعد</div>
                            <div class='wallet-value'>" . number_format($finalVaultBalance) . "</div>
                        </div>
                        <div class='col-4 text-center'>
                            <div class='wallet-label'>التغيير</div>
                            <div class='wallet-change' style='color:{$vaultChangeColor};background:#fff;border-radius:8px;padding:4px 8px;display:inline-block;'>{$vaultChangeSign}" . number_format($vaultChange) . "</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-md-6'>
                <div class='wallet-card wallet-app'>
                    <div class='wallet-label'>💰 محفظة التطبيق (app_wallet)</div>
                    <div class='row mt-2'>
                        <div class='col-4 text-center'>
                            <div class='wallet-label'>قبل</div>
                            <div class='wallet-value'>" . number_format($this->initialAppWalletBalance) . "</div>
                        </div>
                        <div class='col-4 text-center'>
                            <div class='wallet-label'>بعد</div>
                            <div class='wallet-value'>" . number_format($finalAppWalletBalance) . "</div>
                        </div>
                        <div class='col-4 text-center'>
                            <div class='wallet-label'>التغيير</div>
                            <div class='wallet-change' style='color:{$appWalletChangeColor};background:#fff;border-radius:8px;padding:4px 8px;display:inline-block;'>{$appWalletChangeSign}" . number_format($appWalletChange) . "</div>
                        </div>
                    </div>
                </div>
            </div>
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
                            <div class='col-6 col-lg-4'>
                                <small>أطول سلسلة خسارة</small>
                                <p class='h5 text-danger mb-0'>{$userData['max_loss_streak']}</p>
                            </div>
                            <div class='col-6 col-lg-4'>
                                <small>أطول سلسلة فوز</small>
                                <p class='h5 text-success mb-0'>{$userData['max_win_streak']}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }
        
        $html .= "</div>

        <!-- توزيع المضاعفات -->
        <div class='card shadow-sm mb-4'>
            <div class='card-body'>
                <h2 class='h4 mb-3'>توزيع المضاعفات لكل مستخدم</h2>
                <div class='table-responsive'>
                    <table class='table table-sm table-bordered align-middle text-center'>
                        <thead class='table-light'>
                            <tr>
                                <th>المستخدم</th>
                                <th class='text-success'>5x</th>
                                <th class='text-success'>10x</th>
                                <th class='text-success'>20x</th>
                                <th class='text-info'>50x</th>
                                <th class='text-info'>100x</th>
                                <th class='text-warning'>250x</th>
                                <th class='text-warning'>500x</th>
                                <th class='text-danger'>1000x</th>
                                <th class='bg-primary text-white'>المجموع</th>
                            </tr>
                        </thead>
                        <tbody>";
        
        foreach ($uniqueUsers as $userData) {
            $totalWins = array_sum($userData['multiplier_counts']);
            $html .= "<tr>
                        <td class='fw-bold'>{$userData['letter']}</td>
                        <td>{$userData['multiplier_counts'][5]}</td>
                        <td>{$userData['multiplier_counts'][10]}</td>
                        <td>{$userData['multiplier_counts'][20]}</td>
                        <td>{$userData['multiplier_counts'][50]}</td>
                        <td>{$userData['multiplier_counts'][100]}</td>
                        <td>{$userData['multiplier_counts'][250]}</td>
                        <td>{$userData['multiplier_counts'][500]}</td>
                        <td>{$userData['multiplier_counts'][1000]}</td>
                        <td class='bg-primary text-white fw-bold'>{$totalWins}</td>
                      </tr>";
        }
        
        // Add totals row
        $totals = [5 => 0, 10 => 0, 20 => 0, 50 => 0, 100 => 0, 250 => 0, 500 => 0, 1000 => 0];
        foreach ($uniqueUsers as $userData) {
            foreach ($userData['multiplier_counts'] as $mult => $count) {
                if (isset($totals[$mult])) {
                    $totals[$mult] += $count;
                }
            }
        }
        $grandTotal = array_sum($totals);
        
        $html .= "<tr class='table-dark fw-bold'>
                        <td>الإجمالي</td>
                        <td>{$totals[5]}</td>
                        <td>{$totals[10]}</td>
                        <td>{$totals[20]}</td>
                        <td>{$totals[50]}</td>
                        <td>{$totals[100]}</td>
                        <td>{$totals[250]}</td>
                        <td>{$totals[500]}</td>
                        <td>{$totals[1000]}</td>
                        <td>{$grandTotal}</td>
                      </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
                                <th>Global Vault قبل</th>
                                <th>Jackpot قبل</th>
                                <th>Medium قبل</th>
                                <th>تغيير Global</th>
                                <th>تغيير Jackpot</th>
                                <th>تغيير Medium</th>
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
            
            $globalChange = ($round['wallet_changes']['global_vault'] ?? 0) >= 0 ? '+' : '';
            $jackpotChange = ($round['wallet_changes']['jackpot_wallet'] ?? 0) >= 0 ? '+' : '';
            $mediumChange = ($round['wallet_changes']['medium_wallet'] ?? 0) >= 0 ? '+' : '';
            
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
                        <td>" . number_format($round['wallets_before']['global_vault'] ?? 0) . "</td>
                        <td>" . number_format($round['wallets_before']['jackpot_wallet'] ?? 0) . "</td>
                        <td>" . number_format($round['wallets_before']['medium_wallet'] ?? 0) . "</td>
                        <td class='" . (($round['wallet_changes']['global_vault'] ?? 0) >= 0 ? 'text-success' : 'text-danger') . "'>{$globalChange}" . number_format($round['wallet_changes']['global_vault'] ?? 0) . "</td>
                        <td class='" . (($round['wallet_changes']['jackpot_wallet'] ?? 0) >= 0 ? 'text-success' : 'text-danger') . "'>{$jackpotChange}" . number_format($round['wallet_changes']['jackpot_wallet'] ?? 0) . "</td>
                        <td class='" . (($round['wallet_changes']['medium_wallet'] ?? 0) >= 0 ? 'text-success' : 'text-danger') . "'>{$mediumChange}" . number_format($round['wallet_changes']['medium_wallet'] ?? 0) . "</td>
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
        $publicPath = public_path("detailed_fairluck_report_v7.html");
        copy($fullPath, $publicPath);
        
        $this->info("📄 تقرير HTML مفصل V7: {$publicPath}");
    }

    private function displayResults()
    {
        $targetRTP = \App\Models\FairLuckSetting::getTargetRTP() * 100;
        
        $this->info("");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🎯 ملخص النتائج - الإصدار السابع V7:");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
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
                'نسبة الفوز' => $userData['attempts'] > 0 ? number_format(($userData['wins'] / $userData['attempts']) * 100, 1) . '%' : '0%',
                'RTP' => number_format($rtp, 1) . '%',
                'الرصيد النهائي' => number_format($userData['final_balance']),
                'صافي الربح/خسارة' => number_format($userData['final_balance'] - $userData['initial_balance']),
                'أعلى مضاعف' => $userData['max_multiplier'] . 'x',
                'مضاعفات ≥250x' => $userData['high_multipliers'],
                'أطول خسارة' => $userData['max_loss_streak'],
                'أطول فوز' => $userData['max_win_streak'],
            ];
            
            $totalAttempts += $userData['attempts'];
            $totalWins += $userData['wins'];
            $totalBets += $userData['total_bet'];
            $totalWinnings += $userData['total_win'];
        }
        
        if (!empty($summaryData)) {
            $this->table(array_keys($summaryData[0]), array_map('array_values', $summaryData));
        }
        
        $overallRTP = $totalBets > 0 ? ($totalWinnings / $totalBets) * 100 : 0;
        $winRate = $totalAttempts > 0 ? ($totalWins / $totalAttempts) * 100 : 0;
        $rtpDiff = $overallRTP - $targetRTP;
        $rtpDiffStr = $rtpDiff >= 0 ? '+' . number_format($rtpDiff, 2) : number_format($rtpDiff, 2);
        
        $this->info("");
        $this->info("📊 إحصائيات النظام:");
        $this->info("   • إجمالي الرهانات: " . number_format($totalBets));
        $this->info("   • إجمالي المكاسب: " . number_format($totalWinnings));
        $this->info("   • صافي ربح التطبيق: " . number_format($totalBets - $totalWinnings));
        $this->info("   • معدل الفوز: " . number_format($winRate, 2) . '%');
        $this->info("   • عدد مرات الفوز: {$totalWins} من {$totalAttempts}");
        $this->info("");
        $this->info("🎯 RTP Analysis:");
        $this->info("   • RTP المستهدف (Target): " . number_format($targetRTP, 2) . '%');
        $this->info("   • RTP الفعلي (Actual): " . number_format($overallRTP, 2) . '%');
        $this->info("   • الفرق: " . $rtpDiffStr . '%');
        
        // Evaluation
        if (abs($rtpDiff) <= 5) {
            $this->info("   ✅ النظام يعمل ضمن المدى المقبول (±5%)");
        } elseif ($rtpDiff > 0) {
            $this->warn("   ⚠️ RTP أعلى من المستهدف - اللاعبون يكسبون أكثر مما هو مخطط");
        } else {
            $this->warn("   ⚠️ RTP أقل من المستهدف - اللاعبون يخسرون أكثر");
        }
        
        // Smart system evaluation
        $this->info("");
        $this->info("🧠 تقييم النظام الذكي:");
        $this->info("   • Cooldown: معطل (0 = users can win back-to-back)");
        $this->info("   • Min Prob When Low: 60% (لا ينخفض أبداً عن 60% من الاحتمالية الطبيعية)");
        $this->info("   • Prize Size Reduction: مفعل (تقليل حجم الجائزة عند انخفاض المحفظة)");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }
}
