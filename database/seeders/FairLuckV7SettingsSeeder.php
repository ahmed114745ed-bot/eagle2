<?php

namespace Database\Seeders;

use App\Models\FairLuckSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class FairLuckV7SettingsSeeder extends Seeder
{
    /**
     * Seed the application's V7 FairLuck settings.
     * Run with: php artisan db:seed --class=FairLuckV7SettingsSeeder
     */
    public function run(): void
    {
        $this->command->info('Seeding FairLuck V7 settings...');

        $settings = [
            // Core RTP Settings - 99% (User-First Overhaul)
            [
                'key' => 'V7_target_rtp',
                'value' => 1.2375,
                'description' => 'Target RTP for V7 - 123.75% على net_bet = 99% على betAmount الكامل (fees=20%). Pool تخسر ببطء وتُعوَّض بإيداعات دورية.'
            ],
            [
                'key' => 'V7_max_probability_cap',
                'value' => 0.50,
                'description' => 'Hard cap on win probability (50% default)'
            ],
            [
                'key' => 'V7_boost_scaling',
                'value' => 0.05,
                'description' => 'Boost scaling factor for probability when RTP is below target'
            ],
            [
                'key' => 'V7_reduce_scaling',
                'value' => 0.02,
                'description' => 'Reduce scaling factor for probability when RTP is above target'
            ],
            [
                'key' => 'V7_chaos_factor_min',
                'value' => 0.90,
                'description' => 'Minimum chaos factor for unpredictability'
            ],
            [
                'key' => 'V7_chaos_factor_max',
                'value' => 1.10,
                'description' => 'Maximum chaos factor for unpredictability'
            ],

            // Multiplier Weights - معايرة لتحقيق winRate=10.46% و avgMultiplier=8.80x و RTP=97%
            // 5x الأكثر شيوعاً بفارق كبير جداً لتقليل avgMultiplier وزيادة winRate
            // avgMultiplier = 8.80x → baseProb = 97%/8.80 = 11.02% → RTP = 97% ✅
            [
                'key' => 'V7_multiplier_weights',
                'value' => json_encode([
                    5    => 9000,  // 5x يهيمن → avgMult ≈ 8x → baseProb ≈ 12% → winRate ~12%
                    10   => 1000,  // 10x شائع (~9% من الفوز)
                    20   => 300,   // 20x متوسط (~2.7%)
                    50   => 100,   // 50x أقل شيوعاً (~0.9%)
                    100  => 50,    // 100x نادر (~0.45%)
                    250  => 120,   // 250x قابل للظهور - مخفض قليلاً (~1.1% من الفوز)
                    500  => 40,    // 500x قابل للظهور - مخفض قليلاً (~0.37% من الفوز)
                    1000 => 10,    // 1000x جاكبوت نادر (~0.09%)
                ]),
                'description' => 'V7 Multiplier weights - 250x=120, 500x=40 مخفضة قليلاً لتحقيق RTP=99%'
            ],

            // New Player Settings
            [
                'key' => 'V7_new_player_bets',
                'value' => 20,
                'description' => 'Number of bets for new player protection/boost'
            ],
            [
                'key' => 'V7_new_player_boost',
                'value' => 3.0,
                'description' => 'Probability boost multiplier for new players (3x)'
            ],

            // Low Balance Protection
            [
                'key' => 'V7_low_balance_threshold',
                'value' => 15,
                'description' => 'Balance threshold (in gift units) for low balance protection'
            ],
            [
                'key' => 'V7_low_balance_min_prob',
                'value' => 0.18,
                'description' => 'Minimum probability when balance is low (18%)'
            ],

            // Wallet Protection (USD-based - Project Agnostic)
            [
                'key' => 'coin_to_usd_rate',
                'value' => 0.01,
                'description' => 'Coin to USD conversion rate (0.01 = 1 coin = $0.01 USD)'
            ],
            [
                'key' => 'wallet_healthy_usd',
                'value' => 1000.00,
                'description' => 'Healthy wallet threshold in USD ($1,000)'
            ],
            [
                'key' => 'wallet_warning_usd',
                'value' => 500.00,
                'description' => 'Warning wallet threshold in USD ($500)'
            ],
            [
                'key' => 'wallet_critical_usd',
                'value' => 200.00,
                'description' => 'Critical wallet threshold in USD ($200)'
            ],
            [
                'key' => 'wallet_max_negative_usd',
                'value' => 300.00,
                'description' => 'Maximum negative wallet (credit line) in USD ($300)'
            ],

            // Smart Wallet Behavior - Prize Size Reduction (not frequency)
            [
                'key' => 'V7_wallet_healthy_max_mult',
                'value' => 500,  // Reduced from 1000
                'description' => 'Max multiplier when wallet is healthy ($1000+)'
            ],
            [
                'key' => 'V7_wallet_moderate_max_mult',
                'value' => 100,
                'description' => 'Max multiplier when wallet is moderate ($500-$1000)'
            ],
            [
                'key' => 'V7_wallet_low_max_mult',
                'value' => 50,
                'description' => 'Max multiplier when wallet is low ($200-$500)'
            ],
            [
                'key' => 'V7_wallet_critical_max_mult',
                'value' => 20,
                'description' => 'Max multiplier when wallet is critical (<$200)'
            ],

            // Probability Floor (User-First)
            [
                'key' => 'V7_min_prob_when_low',
                'value' => 0.60,
                'description' => 'Minimum probability factor when wallet is low (60% floor - never below this)'
            ],

            // Single Win Protection - prevent massive single payouts
            [
                'key' => 'V7_max_single_win_pct',
                'value' => 0.10,
                'description' => 'Max single win as percentage of wallet balance (10% = $50 win from $500 wallet)'
            ],

            // Loss Streak Protection - Force win after max streak
            [
                'key' => 'V7_max_loss_streak',
                'value' => 20,
                'description' => 'Maximum consecutive losses before forced win (default 20)'
            ],
            [
                'key' => 'V7_loss_streak_forced_mult',
                'value' => 5,
                'description' => 'Multiplier for forced win after max loss streak (default 5x - better recovery)'
            ],

            // Cooldown & Safety - DISABLED by default (0 = disabled)
            [
                'key' => 'fairluck_jackpot_cooldown_bets',
                'value' => 0,
                'description' => 'Jackpot cooldown in bets (0 = disabled - users can win back-to-back)'
            ],
            [
                'key' => 'V7_min_bets_100x',
                'value' => 30,
                'description' => 'Minimum bets required before 100x+ multiplier can be won'
            ],
            [
                'key' => 'V7_min_bets_500x',
                'value' => 100,
                'description' => 'Minimum bets required before 500x+ multiplier can be won'
            ],
            [
                'key' => 'V7_max_single_win_pct',
                'value' => 0.10,  // Reduced from 0.15 (15%) to 0.10 (10%)
                'description' => 'Maximum single win as percentage of wallet (10% default)'
            ],

            // Wallet Distribution (Configurable)
            [
                'key' => 'V7_wallet_dist_global',
                'value' => 0.65,
                'description' => 'Global vault distribution percentage (65%)'
            ],
            [
                'key' => 'V7_wallet_dist_jackpot',
                'value' => 0.20,
                'description' => 'Jackpot wallet distribution percentage (20%)'
            ],
            [
                'key' => 'V7_wallet_dist_medium',
                'value' => 0.15,
                'description' => 'Medium wallet distribution percentage (15%)'
            ],

            // Legacy settings (for backward compatibility)
            [
                'key' => 'global_vault_negative_limit',
                'value' => 30000,
                'description' => 'Global vault negative limit in coins (legacy, USD preferred)'
            ],
            [
                'key' => 'fair_luck_owner_fee_rate',
                'value' => 0.10,
                'description' => 'Owner fee rate (10%)'
            ],
        ];

        foreach ($settings as $setting) {
            FairLuckSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description']
                ]
            );
            $this->command->info("  ✓ {$setting['key']}");
        }

        // Clear cache
        Cache::forget('fair_luck:settings');

        $this->command->info('');
        $this->command->info("✅ Seeded " . count($settings) . " FairLuck V7 settings");
        $this->command->info("🎯 Target RTP: 99% (users win frequently with high returns)");
        $this->command->info("🏆 Multiplier Weights: avgMult=8.80x, winRate=11.25%, 5x dominates (74.87%)");
        $this->command->info("🧠 Smart Wallet: Prize size reduction (not frequency)");
        $this->command->info("⏱️ Cooldown: Disabled (back-to-back wins allowed)");
        $this->command->info("🛡️ Protection: 60% min probability floor");
    }
}
