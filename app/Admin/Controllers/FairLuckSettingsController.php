<?php

namespace App\Admin\Controllers;

use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\FairLuckWallet;
use App\Models\FairLuckWalletHistory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FairLuckSettingsController extends AdminController
{
    protected $title = 'FairLuck Settings & Reports';

    public function index(Content $content)
    {
        $settings = FairLuckSetting::pluck('value', 'key')->toArray();
        $wallets = FairLuckWallet::all();

        $history = FairLuckWalletHistory::where('wallet_type', 'global_vault')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()->reverse()->values();


        $transactions = FairLuckTransaction::with(['user', 'gift'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return $content
            ->title('FairLuck Dashboard')
            ->description('Manage settings and view wallet statistics')
            ->body(view('admin.fairluck.dashboard', [
                'settings' => $settings,
                'wallets' => $wallets,
                'history' => $history,
                'transactions' => $transactions
            ]));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'V7_target_rtp' => 'nullable|numeric|between:70,99',
            'V7_max_probability_cap' => 'nullable|numeric|between:10,100',
            'V7_boost_scaling' => 'nullable|numeric|between:0,100',
            'V7_reduce_scaling' => 'nullable|numeric|between:0,100',
            'V7_chaos_factor_min' => 'nullable|numeric|between:0,100',
            'V7_chaos_factor_max' => 'nullable|numeric|between:0,200',
            'V7_new_player_bets' => 'nullable|integer|between:5,100',
            'V7_new_player_boost' => 'nullable|numeric|between:1.0,5.0',
            'V7_low_balance_threshold' => 'nullable|integer|between:5,50',
            'V7_low_balance_min_prob' => 'nullable|numeric|between:0,100',
            'coin_to_usd_rate' => 'nullable|numeric|between:0.0001,1.0000',
            'wallet_healthy_usd' => 'nullable|numeric|min:100',
            'wallet_warning_usd' => 'nullable|numeric|min:50',
            'wallet_critical_usd' => 'nullable|numeric|min:0',
            'wallet_max_negative_usd' => 'nullable|numeric|min:0',
            'V7_wallet_healthy_max_mult' => 'nullable|integer|between:100,1000',
            'V7_wallet_moderate_max_mult' => 'nullable|integer|between:50,500',
            'V7_wallet_low_max_mult' => 'nullable|integer|between:10,100',
            'V7_wallet_critical_max_mult' => 'nullable|integer|between:5,50',
            'V7_min_prob_when_low' => 'nullable|numeric|between:0,100',
            'fairluck_jackpot_cooldown_bets' => 'nullable|integer|between:0,1000',
            'V7_min_bets_100x' => 'nullable|integer|between:10,100',
            'V7_min_bets_500x' => 'nullable|integer|between:50,500',
            'V7_max_single_win_pct' => 'nullable|numeric|between:0,100',
            'V7_wallet_dist_global' => 'nullable|numeric|between:0,100',
            'V7_wallet_dist_jackpot' => 'nullable|numeric|between:0,100',
            'V7_wallet_dist_medium' => 'nullable|numeric|between:0,100',
            'global_vault_negative_limit' => 'nullable|numeric',
            'fair_luck_owner_fee_rate' => 'nullable|numeric|between:0,100',
            'fair_luck_app_fee_rate' => 'nullable|numeric|between:0,100',
            'fair_luck_receiver_fee_rate' => 'nullable|numeric|between:0,100',
        ]);

        // Handle all standard settings
        $standardKeys = [
            'V7_target_rtp',
            'V7_max_probability_cap',
            'V7_boost_scaling',
            'V7_reduce_scaling',
            'V7_chaos_factor_min',
            'V7_chaos_factor_max',
            'V7_new_player_bets',
            'V7_new_player_boost',
            'V7_low_balance_threshold',
            'V7_low_balance_min_prob',
            'coin_to_usd_rate',
            'wallet_healthy_usd',
            'wallet_warning_usd',
            'wallet_critical_usd',
            'wallet_max_negative_usd',
            'V7_wallet_healthy_max_mult',
            'V7_wallet_moderate_max_mult',
            'V7_wallet_low_max_mult',
            'V7_wallet_critical_max_mult',
            'V7_min_prob_when_low',
            'fairluck_jackpot_cooldown_bets',
            'V7_min_bets_100x',
            'V7_min_bets_500x',
            'V7_max_single_win_pct',
            'V7_wallet_dist_global',
            'V7_wallet_dist_jackpot',
            'V7_wallet_dist_medium',
            'global_vault_negative_limit',
            'fair_luck_owner_fee_rate',
            'fair_luck_app_fee_rate',
            'fair_luck_receiver_fee_rate',
        ];

        // Percentage fields that need to be converted from percentage to decimal
        $percentageFields = [
            'V7_target_rtp',
            'V7_max_probability_cap',
            'V7_boost_scaling',
            'V7_reduce_scaling',
            'V7_chaos_factor_min',
            'V7_chaos_factor_max',
            'V7_low_balance_min_prob',
            'V7_min_prob_when_low',
            'V7_max_single_win_pct',
            'V7_wallet_dist_global',
            'V7_wallet_dist_jackpot',
            'V7_wallet_dist_medium',
            'fair_luck_owner_fee_rate',
            'fair_luck_app_fee_rate',
            'fair_luck_receiver_fee_rate',
        ];

        foreach ($standardKeys as $key) {
            if ($request->has($key)) {
                $value = $request->input($key);
                
                // Convert percentage to decimal for percentage fields
                if (in_array($key, $percentageFields) && $value !== null && $value !== '') {
                    $value = (float)$value / 100;
                }
                
                FairLuckSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        // Handle multiplier weights (array)
        if ($request->has('V7_multiplier_weights')) {
            $weights = $request->input('V7_multiplier_weights');
            FairLuckSetting::updateOrCreate(
                ['key' => 'V7_multiplier_weights'],
                ['value' => json_encode($weights)]
            );
        }

        // Clear the cache so changes take effect immediately
        \Illuminate\Support\Facades\Cache::forget('fair_luck:settings');

        admin_success(__('Updated'), __('Settings updated successfully.'));
        return back();
    }
}
