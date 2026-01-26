<?php

namespace Modules\UsersWallet\Http\Controllers\Api;

use App\Helpers\ShippingAgencyHelper;
use App\Models\ShippingAgency;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Support\Renderable;
use Modules\UsersWallet\Helpers\WalletHelper;
use Modules\UsersWallet\Services\WalletService;

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
            
            return Common::apiResponse(false, $e->getMessage(), null, 500);
        }
    }


    public function transferToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'amount'     => 'required|numeric|min:0.01',
        ]);

        if ($validator->errors()->has('user_id')) {
            return Common::apiResponse(
                false,
                __('this agency does not have owner'),
                null,
                400
            );
        }

        if ($validator->fails()) {
            return Common::apiResponse(
                false,
                $validator->errors()->first(),
                null,
                400
            );
        }

        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        if ($stop_all_charge == 1) return Common::apiResponse(0, __('api_responses.freeze_charge_settings'), 404);

        $from = $request->user();
        $to = User::find($request->user_id);
        
        if (!$to) {
            return Common::apiResponse(0, __('api_responses.user_not_found'), 404);
        }
        
        if ($from->transfer_salary == 1)  return Common::apiResponse(0, __('api_responses.freeze_transfer_charger'), 404);


        Common::checkUserAgencyFrozen($from);

        if ($to->transfer_salary == 1) return Common::apiResponse(0, __('api_responses.freeze_transfer_receiver'), 404);

        $rate = Common::getCoinsValue('user_coins');

        if (!$rate) return Common::apiResponse(0, __('please set usd_value_in_coins in configs'), 422);
        $usd = $request->amount;
        $coins = $usd * $rate;
        return $this->handleRequest(function () use ($request, $coins,$usd) {
            $from = $request->user();
            $result = $this->walletService->transfer(
                Auth::id(),
                $request->user_id,
                $coins,
                $usd 
            );


            $data = ['coins' => (string)$from->di, 'usd' => (string)$from->user_wallet_balance,];
            if ($result['status'] === 'success') {
                return Common::apiResponse(true, __('Transfer completed successfully'), $data, 200);
            }

            return Common::apiResponse(false, $result['message'] ?? __('Transfer failed'));
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
                __('withdrawal_request_created'),
                $withdrawal
            );
        });
    }





     public function agencyRequestWithdrawal(Request $request)
    {
        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        if ($stop_all_charge == 1) {
            return Common::apiResponse(0, __('api_responses.freez_charge'), 404);
        }

        $toId = $request->to_id;
        $from = $request->user();

        if ($from->transfer_salary == 1) {
            return Common::apiResponse(0, __('api_responses.freeze_transfer_charger'), 404);
        }

        Common::checkUserAgencyFrozen($from);

        $to = Common::searchAgency($toId);
        if (!$to) {
            return Common::apiResponse(0, 'Not allowed To this agency or this not an agency', 422);
        }

        if (!ShippingAgencyHelper::isVerifiedChargeForAgency($to)) {
            return Common::apiResponse(0, __('not_verified_agency'), 403);
        }

        if ($to->is_frozen == 1) {
            return Common::apiResponse(0, __('api_responses.frozen_agency'), 404);
        }

        $usd = floatval($request->usd);

        if ($usd <= 0) {
            return Common::apiResponse(0, 'This value is not allowed', 422);
        }

        $rate = Common::getCoinsValue('shipping_coins');

        if (!$rate) {
            return Common::apiResponse(0, 'please set usd_value_in_coins in configs', 422);
        }
        
        $coins = $usd * $rate;
        
        $available = wallet_available_by_user($from->id);
        
        if ($available < $usd) {
            return Common::apiResponse(0, 'balance not enough', 407);
        }

        DB::beginTransaction();
        try {
            $this->chargeToAgencyFromWallet($from, $to, $coins, $usd);

            $newAvailable = wallet_available_by_user($from->id);
            $data = [
                'usd' => (string)$newAvailable,
            ];

            DB::commit();
            return Common::apiResponse(1, 'success', $data, 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return Common::apiResponse(0, $exception->getMessage(), 400);
        }
    }

    public function chargeToAgencyFromWallet(User $fromUser, ShippingAgency $toAgency, $coins, $usd)
    {
        $chargeType = 'user';

        $wallet = \Modules\UsersWallet\Entities\UserWallet::firstOrCreate(['user_id' => $fromUser->id]);
        $available = wallet_available_by_wallet($wallet);

        if ($available < $usd) {
            throw new \Exception('Insufficient balance.');
        }

        $wallet->cut_amount += $usd;
        $wallet->save();
     
        $toAgency->increment('coins', $coins);

        \Modules\UsersWallet\Entities\WalletLog::create([
            'wallet_id' => $wallet->id,
            'user_id' => $fromUser->id,
            'amount' => -$usd,
            'operation' => 'withdraw_to_shipping_agency',
            'type' => 'user',
            'before_amount' => $available,
            'after_amount' => wallet_available_by_user($fromUser->id),
            'related_id' => $toAgency->id,
        ]);


    }
}
