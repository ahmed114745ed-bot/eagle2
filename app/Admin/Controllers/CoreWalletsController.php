<?php

namespace App\Admin\Controllers;

use App\Models\CoreWallets;
use App\Models\CoreWalletTransaction;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\Box;

use function Laravel\Prompts\error;

class CoreWalletsController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */


    public $permission_name = 'app-wallet';

    // public function index(Content $content)
    // {
    //     return parent::index($content
    //         ->title(trans('Application wallet'))
    //         ->row(function (Row $row) {
    //             $row->column(12, view('admin.grid.common.CoreWallet'));
    //         })
    //         // ->row(function (Row $row) {
    //         //     $row->column(12, $this->grid());
    //         // })
    //     );
    // }

    public function index(Content $content)
    {
        $icons = [
            'app_wallet' => 'fa-solid fa-coins',
            'owner_wallet' => 'fa-solid fa-user-tie',
            'game_wallet' => 'fa-solid fa-dice',
            'lucky_box' => 'fa-solid fa-box-open',
            'host_agency' => 'fa-solid fa-building',
            'agency' => 'fa-solid fa-briefcase',
            'lucky_gifts' => 'fa-solid fa-gift',
            'chinese_games' => 'fa-solid fa-dragon',
            'games' => 'fa-solid fa-gamepad',
            'shipping_agents' => 'fa-solid fa-truck',
            'payment_gateways' => 'fa-solid fa-credit-card',
            'mall' => 'fa-solid fa-store',
            'vip' => 'fa-solid fa-crown',
            'ads' => 'fa-solid fa-rectangle-ad'
        ];

        $canTransfer = Admin::user()->can('*') || Admin::user()->can('transfer-switch-app-wallet');

        return parent::index($content
            ->title(__('Application wallet'))
            ->body(view('admin.core_wallets.index', [
                'coreWallets' => CoreWallets::get(),
                'icons' => $icons,
                'canTransfer' => $canTransfer
            ])));
    }

    protected function grid2()
    {
        $form = new Box();

        $form->view('admin.grid.common.CoreWallet');

        return $form;
    }

    protected function submitTransfer(Request $request)
    {
        $request->validate([
            'from_wallet_id' => 'required',
            'to_wallet_id' => 'required',
            'amount' => 'required|numeric|min:1',
        ]);

        $adminId = auth()->id();
        $fromWallet = CoreWallets::find($request->from_wallet_id);
        if ($fromWallet->coins < $request->amount) {
             return response()->json([
                'status' => 0,
                'message' => 'plz check coins wallet',
            ]);
        }
        $fromWallet->coins -= $request->amount;
        $fromWallet->save();
        $ToWallet = CoreWallets::find($request->to_wallet_id);
        $ToWallet->coins += $request->amount;
        $ToWallet->save();

        CoreWalletTransaction::create([
            'from_wallet' => $request->from_wallet_id,
            'to_wallet' => $request->to_wallet_id,
            'amount' => $request->amount,
            'admin_id' => $adminId,
        ]);
        return response()->json([
            'status' => 1,
            'message' => 'Done',
        ]);
    }
}
