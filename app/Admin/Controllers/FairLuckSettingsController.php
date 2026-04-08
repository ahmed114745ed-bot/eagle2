<?php

namespace App\Admin\Controllers;

use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\FairLuckWallet;
use App\Models\FairLuckWalletHistory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class FairLuckSettingsController extends AdminController
{
    protected $title = 'FairLuck V7 Settings';

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
            ->title('FairLuck V7 Dashboard')
            ->description('Single-step weighted selection engine settings')
            ->body(view('admin.fairluck.dashboard', [
                'settings' => $settings,
                'wallets' => $wallets,
                'history' => $history,
                'transactions' => $transactions
            ]));
    }

    public function saveSettings(Request $request)
    {
        // Percentage fields: submitted as 0-100, stored as 0-1
        $percentageFields = [
            'V7_target_rtp',
            'fair_luck_owner_fee_rate',
            'fair_luck_app_fee_rate',
            'fair_luck_receiver_fee_rate',
        ];

        // Direct numeric fields: stored as-is
        $numericFields = [
            'V7_rtp_activation',
            'V7_max_loss_streak',
            'V7_forced_win_mult',
            'V7_wallet_min',
            'V7_wallet_tight',
            'V7_wallet_target',
            'V7_wallet_high',
            'V7_wallet_drain',
            'V7_negative_limit',
            'V7_nowin_sensitivity',
            'V7_win_base_sensitivity',
            'V7_win_position_sensitivity',
            'V7_boost_base_sensitivity',
            'V7_boost_position_sensitivity',
            'V7_wallet_weight',
            'V7_rtp_weight',
            'V7_nowin_floor',
            'coin_to_usd_rate',
            'global_vault_negative_limit',
        ];

        // Save percentage fields (convert % → decimal)
        foreach ($percentageFields as $key) {
            if ($request->has($key)) {
                $value = (float) $request->input($key) / 100;
                FairLuckSetting::updateOrCreate(['key' => $key], ['value' => $value]);

                // Keep V7_app_fee_rate in sync with fair_luck_app_fee_rate
                if ($key === 'fair_luck_app_fee_rate') {
                    FairLuckSetting::updateOrCreate(['key' => 'V7_app_fee_rate'], ['value' => $value]);
                }
            }
        }

        // Save numeric fields as-is
        foreach ($numericFields as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);

                // Validate wallet_weight + rtp_weight <= 1.0
                if ($key === 'V7_wallet_weight' || $key === 'V7_rtp_weight') {
                    $otherKey = $key === 'V7_wallet_weight' ? 'V7_rtp_weight' : 'V7_wallet_weight';
                    $otherVal = $request->has($otherKey) ? (float) $request->input($otherKey) : (float) ($settings[$otherKey] ?? 0.5);
                    if (((float) $val + $otherVal) > 1.05) { // Small tolerance for float
                        admin_warning(__('Warning'), __('wallet_weight + rtp_weight should sum to 1.0. Current sum: ') . round((float) $val + $otherVal, 2));
                    }
                }

                // Keep global_vault_negative_limit in sync with V7_negative_limit
                if ($key === 'V7_negative_limit') {
                    FairLuckSetting::updateOrCreate(['key' => 'global_vault_negative_limit'], ['value' => $val]);
                }

                FairLuckSetting::updateOrCreate(['key' => $key], ['value' => $val]);
            }
        }

        // Save base weights (JSON string)
        if ($request->has('V7_base_weights')) {
            $raw = $request->input('V7_base_weights');
            // Validate JSON
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                FairLuckSetting::updateOrCreate(
                    ['key' => 'V7_base_weights'],
                    ['value' => json_encode($decoded)]
                );
            }
        }

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('fair_luck:settings');

        admin_success(__('Updated'), __('V7 settings saved successfully.'));
        return back();
    }
}
