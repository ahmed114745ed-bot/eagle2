<?php

namespace Modules\AreaManager\Http\Controllers;

use App\Models\AdminCoinRate;
use App\Services\CoinRateService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Auth;

class CoinRateController extends Controller
{
    public function index(Content $content)
    {
        $adminId = Auth::id();
        $customRate = AdminCoinRate::where('admin_id', $adminId)->first();
        $appRate = CoinRateService::getAppBaseRate();

        return $content
            ->header(__('Coin Rate Settings'))
            ->description(__('Set your own coin rate for charging'))
            ->body(view('areamanager::coin_rate_settings', [
                'customRate' => $customRate?->rate,
                'appRate' => $appRate
            ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0'
        ]);

        AdminCoinRate::updateOrCreate(
            ['admin_id' => Auth::id()],
            ['rate' => $request->rate]
        );

        return back()->with('success', __('Settings updated successfully'));
    }
}
