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
        $data = $request->only([
            'global_vault_negative_limit',
            'fair_luck_app_fee_rate',
            'fair_luck_receiver_fee_rate'
        ]);

        foreach ($data as $key => $value) {
            FairLuckSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
