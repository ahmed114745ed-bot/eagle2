<?php

namespace Modules\UsersWallet\Http\Controllers\Api;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\UsersWallet\Entities\UserWithdrawal;
use Modules\UsersWallet\Helpers\WalletHelper;
use Modules\UsersWallet\Services\WalletService;
use Illuminate\Support\Facades\Auth;

class UsersWalletController extends Controller
{

    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }


    private function handleRequest(callable $callback)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            \Log::info(123339999999999);
            return Common::apiResponse(false, $e->getMessage(), null, 500);
        }
    }


    public function transferToUser(Request $request)
    {
       \Log::info(12333444444444);
        if (!$request->user_id) return Common::apiResponse(false, __('this agency does not have owner'), null, 500);
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'amount'     => 'required|numeric|min:0.01',
        ]);
       \Log::info('Withdrawal request data:', $request->all());

        return $this->handleRequest(function () use ($request) {
            $from = $request->user();
            $result = $this->walletService->transfer(
                Auth::id(),
                $request->user_id,
                $request->amount
            );


            $data = ['coins' => (string)$from->di, 'usd' => (string)$from->user_wallet_balance,];
            if ($result['status'] === 'success') {
                return Common::apiResponse(true, 'Transfer completed successfully', $data, 200);
            }

            return Common::apiResponse(false, $result['message'] ?? 'Transfer failed');
        });
    }


    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'meta'   => 'nullable|array',
        ]);

        return $this->handleRequest(function () use ($request) {
            $withdrawal = WalletHelper::createWithdrawal(
                Auth::id(),
                $request->amount,
                $request->meta ?? []
            );

            return Common::apiResponse(
                true,
                'Withdrawal request created successfully. Status: pending.',
                $withdrawal
            );
        });
    }
}
