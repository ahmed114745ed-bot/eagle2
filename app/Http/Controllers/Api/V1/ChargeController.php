<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ChargeRecievedInfoResource;
use App\Http\Resources\Api\V1\ChargeResource;
use App\Http\Resources\Api\V1\ChargeResourceforAgencyCharge;
use App\Http\Resources\Api\V1\TrxResource;
use App\Models\User;
use App\Tik\Services\ChargeRepoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Achievement\Http\Services\UserAchievementService;


class ChargeController extends Controller
{

    protected $chargeService;

    public function __construct(ChargeRepoService $chargeService)
    {
        $this->chargeService = $chargeService;
    }


    public function charge_co_for_owner(Request $request)
    {
        //done
        $user = $request->user();
        $count = $request->amount;
        $userUuid = $request->id;
//        if ($user->charge_status == 0) {
//            return Common::apiResponse(0, __('api.freez_charge'), 404);
//        }
        if ($count < 0 || !is_numeric($count)) {
            return Common::apiResponse(0, 'this value not allow', 422);
        }
        if (!$userUuid || !$count) {
            return Common::apiResponse(0, __('api_responses.missing_params'), 404);
        }
        try {
            $this->chargeService->chargeCoinsFromOwner($count, $user->id, $userUuid);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }


        return Common::apiResponse(true, 'Your recharge was successful');
    }


    public function chargeTo(Request $request)
    {
        $app_feature = \Cache::get('host_agency');
        if (!$app_feature){
            throw new Exception(__('Agency Feature is Disabled, Contact the administration'));
        }

        $types = [
            'user' => [$this, 'chargeToUser'],
            'agency' => [$this, 'chargeToAgency']
        ];

        $type = $request->input('type');
        $instance = $types[$type] ?? $types['agency'];

        if (!$instance) {
            return Common::apiResponse(0, 'Type Not Found', 400);
        }
        $data = call_user_func($instance, $request);
        return $data;

    }

    public function chargeToUser(Request $request)
    {
        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        if ($stop_all_charge == 1) {
            return Common::apiResponse(0, __('api_responses.freeze_charge_settings'), 404);
        }
        $toId = $request->to_id;
        $from = $request->user();
        $isRoomTarget = false;
        $to = User::find($toId);

        if (!$to) return Common::apiResponse(0, 'User Not Found', 400);

        if ($from->transfer_salary == 1) {
            return Common::apiResponse(0, __('api_responses.freeze_transfer_charger'), 404);
        }

        if ($to->transfer_salary == 1) {
            return Common::apiResponse(0, __('api_responses.freeze_transfer_receiver'), 404);
        }

        if (!$to) Common::apiResponse(0, __('user not found'), 404);



        $usd = $request->usd;

        if (!is_numeric($usd) || $usd < 0 || fmod($usd, 1) != 0) {
            return Common::apiResponse(0, 'This value is not allowed', 422);
        }

        if (!$usd || !$to) {
            return Common::apiResponse(0, 'not found', 404);
        }
        $rate = Common::getCoinsValue('user_coins');

        if (!$rate) {
            return Common::apiResponse(0, 'please set usd_value_in_coins in configs', 422);
        }
        $coins = $usd * $rate;

        $totalSalary = $from->salary;
        $roomSalary = $from->ownerRoom?->salary;
        if ($totalSalary < $usd) {
            return Common::apiResponse(0, 'balance not enough', 407);
        } else if ($roomSalary >= $usd) {
            $isRoomTarget = (bool)$from->ownerRoom;
        }
        DB::beginTransaction();
        try {

            $this->chargeService->chargeTo($from, $to, $coins, $isRoomTarget, $usd);
            $data = ['coins' => (string)$from->di, 'usd' => (string)$from->salary,];

            DB::commit();
            // return $data;
            return Common::apiResponse(1, 'success', $data, 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return Common::apiResponse(0, $exception->getMessage(), 400);
        }
    }




    public function chargeToAgency(Request $request)
    {
        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        if ($stop_all_charge == 1) {
            return Common::apiResponse(0, __('api_responses.freez_charge'), 404);
        }


        $toId = $request->to_id;
        $from = $request->user();
        $isRoomTarget = false;

        $to = Common::searchAgency($toId);
        if (!$to) return Common::apiResponse(0, 'Not allowed To this agency or this not an agency', 422);
        if ($to->is_frozen == 1) {
            return Common::apiResponse(0, __('api_responses.frozen_agency'), 404);
        }

        $usd = $request->usd;

        if (!is_numeric($usd) || $usd < 0 || fmod($usd, 1) != 0) {
            return Common::apiResponse(0, 'This value is not allowed', 422);
        }

        if (!$usd || !$to) {
            return Common::apiResponse(0, 'not found', 404);
        }
        $rate = Common::getCoinsValue('shipping_coins');

        if (!$rate) {
            return Common::apiResponse(0, 'please set usd_value_in_coins in configs', 422);
        }
        $coins = $usd * $rate;
        $totalSalary = $from->salary;
        $roomSalary = $from->ownerRoom?->salary;
        if ($totalSalary < $usd) {
            return Common::apiResponse(0, 'balance not enough', 407);
        } else if ($roomSalary >= $usd) {
            $isRoomTarget = (bool)$from->ownerRoom;
        }
        DB::beginTransaction();
        try {

            $this->chargeService->chargeToAgency($from, $to, $coins, $isRoomTarget, $usd);
            $data = ['coins' => (string)$from->di, 'usd' => (string)$from->salary,];

            DB::commit();
            return Common::apiResponse(1, 'success', $data, 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return Common::apiResponse(0, $exception->getMessage(), 400);
        }
    }



    public function sendMoneyFoeHost(Request $request)
    {
        //done
        //        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        //        if ($stop_all_charge == 1) {
        //            return Common::apiResponse(0, __('api.freez_charge'), 404);
        //        }

        $user = $request->user();
        $count = $request->amount;
        $userUuid = $request->user_id;

        if ($count < 0 || !is_numeric($count)) {
            return Common::apiResponse(0, 'this value not allow', 422);
        }

        if ($user->di < $count) {
            return Common::apiResponse(0, 'balance not enough');
        }

        try {
            $userReceiver = $this->chargeService->sendMoney($user, $userUuid, $count);
            if ($userReceiver instanceof User) {
                (new UserAchievementService())->insertCharging($userReceiver, $count);
            }
            UserCommon::UserEarnedInvitation($userReceiver->id, $count);
            $data = ['coins' => (string)$user->di, 'usd' => (string)$user->salary,];
            return Common::apiResponse(1, 'your recharge was successful', $data, 200);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage());
        }
    }

    public function chargeCoForUsersHistory(Request $request)
    {
        $userId = $request->user()->id;
        if (!$request->type) return Common::apiResponse(0, 'missing params', null, 422);
        $charge = $this->chargeService->getChargeUserHistory(userId: $userId, type: $request->type, chargeType: 'freight forwarder');
        return Common::apiResponse(1, '', ChargeResourceforAgencyCharge::collection($charge), 200);
    }


    public function ChargeDollarForOwner(Request $request)
    {
        $app_feature = \Cache::get('host_agency');
        if (!$app_feature){
            throw new Exception(__('Agency Feature is Disabled, Contact the administration'));
        }
        $stop_all_charge = settings()->get("stop_charge") ?? 0;
        if ($stop_all_charge == 1) {
            return Common::apiResponse(0, __('api_responses.freeze_charge_settings'), 404);
        }
        $types = [
            'user' => [$this, 'ChargeDollarForOwner_to_users'],
            'agency' => [$this, 'ChargeDollarForOwner_to_agency']
        ];

        $type = $request->input('type');
        $instance = $types[$type] ?? $types['user'];
        if (!$instance) {
            return Common::apiResponse(0, 'Type Not Found', 400);
        }

        $data = call_user_func($instance, $request);
        return $data;
    }


    public function ChargeDollarForOwner_to_users(Request $request)
    {
        //done
        //        return Common::apiResponse(0, 'try again');
        $user = $request->user();
        $count = $request->amount;
        $userUuid = $request->id;
        // if ($user->transfer_salary == 1) {
        //     return Common::apiResponse(0, __('api.freez_charge'), 404);
        // }
        if ($count < 0 || !is_numeric($count)) {
            return Common::apiResponse(0, 'this value not allow', 422);
        }
        if (!$userUuid || !$count) {
            return Common::apiResponse(0, __('api_responses.missing_params'), 404);
        }

        try {
            [$receiver, $amount, $salary] = $this->chargeService->chargeDollarForOwner($user, $userUuid, $count);

            if ($user instanceof User) {
                (new UserAchievementService())->insertCharging($receiver, $amount);
            }
            UserCommon::UserEarnedInvitation($receiver->id, $amount);
            $data = ['coins' => (string)$user->di, 'usd' => (string)$salary,];
            return Common::apiResponse(1, 'Your recharge was successful', $data, 200);
        } catch (Exception $e) {

            return Common::apiResponse(0, $e->getMessage(), 400);
        }
    }


    public function ChargeDollarForOwner_to_agency(Request $request)
    {
        //done
        //        return Common::apiResponse(0, 'try again');
        $user = $request->user();
        $count = $request->amount;
        $userUuid = $request->id;
        // if ($user->transfer_salary == 1) {
        //     return Common::apiResponse(0, __('api.freez_charge'), 404);
        // }

        if ($count < 0 || !is_numeric($count)) {
            return Common::apiResponse(0, 'this value not allow', 422);
        }
        if (!$userUuid || !$count) {
            return Common::apiResponse(0, __('api_responses.missing_params'), 404);
        }
        $receiver = Common::searchAgency($userUuid);
        if ($receiver == false) return Common::apiResponse(0, 'this  not found', 422);
        if ($receiver->is_frozen == 1) {
            return Common::apiResponse(0, __('api_responses.frozen_agency'), 404);
        }
      
        try {
            [$receiver, $amount, $salary] = $this->chargeService->chargeDollarForOwner_to_agency($user, $userUuid, $count);

            // if ($user instanceof User) {
            //     (new UserAchievementService())->insertCharging($receiver, $amount);
            // }
            // UserCommon::UserEarnedInvitation($receiver->id, $amount);
            $data = ['coins' => (string)$user->di, 'usd' => (string)$salary,];
            return Common::apiResponse(1, 'Your recharge was successful', $data, 200);
        } catch (Exception $e) {

            return Common::apiResponse(0, $e->getMessage(), 400);
        }
    }

    public function chargeDollarHistory(Request $request)
    {

        $userId = $request->user()->id;
        if (!$request->type) return Common::apiResponse(0, 'missing params', null, 422);
        $charge = $this->chargeService->getChargeUserHistory(userId: $userId, type: $request->type, chargeType: 'Host agent');
        return Common::apiResponse(1, '', ChargeResourceforAgencyCharge::collection($charge), 200);
    }

    public function chargeHistory(Request $request)
    {
        $userId = $request->user()->id;
        $charge = $this->chargeService->getChargeUserHistory($userId, $request->type, $request->by_date, 'Host agent');

        return Common::apiResponse(1, '', ChargeResource::collection($charge), 200);
    }


    public function userChargeCoins(Request $request)
    {
        $userId = $request->user()->id;
        $searchKey = $request->search_key ?? null;
        $charge = $this->chargeService->getChargeUserHistory($userId, 'received', null, 'Host agent');

        $charge = $charge->with(['sender'])->when($searchKey, fn($query) => $query->whereHas('sender', fn($q) => $q->where('uuid', 'like', $searchKey)));
        if ($request->by_date) {
            $charge = $charge->where('created_at', 'like', "%$request->by_date%");
        }
        return Common::apiResponse(1, '', ChargeRecievedInfoResource::collection($charge->orderByDesc('created_at')->get()), 200);
    }

    public function userChargeCoinsII(Request $request)
    {
        $userId = $request->user()->id;
        $searchKey = $request->search_key ?? null;
        $charge = $this->chargeService->getChargeUserHistory($userId, 'received', $request->by_date, 'Host agent', $searchKey);

        return Common::apiResponse(1, '', ChargeRecievedInfoResource::collection($charge), 200);
    }


    public function trxLog(Request $request)
    {
        $user = $request->user();
        $searchKey = $request->search_key ?? null;
        $trx = $this->chargeService->getCoinLogs($user->id, $searchKey);
        return Common::apiResponse(1, '', TrxResource::collection($trx), 200);
    }


    public function getUserAgency(Request $request)
    {
        $data = $this->chargeService->userAgencySearch($request);
        return Common::apiResponse(1, '', $data, 200);
    }



    public function chargeFromAgencyToAnother(Request $request)
    {
        $from = $request->user();

        try {
            $this->chargeService->chargeAgencyToAnother($from, $request);

            return Common::apiResponse(1, 'your recharge was successful', 200);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage());
        }
    }



    public function chargeToHistory(Request $request)
    {
 

        $types = [
            'user' => [$this, 'chargeToUserHistory'],
            'agency' => [$this, 'chargeToAgencyHistory']
        ];

        $type = $request->input('type');
        $instance = $types[$type] ?? $types['user'];

        if (!$instance) {
            return Common::apiResponse(0, 'Type Not Found', 400);
        }
        $data = call_user_func($instance, $request);
        return $data;

    }
    

    public function chargeToUserHistory($request){
        $userId = auth()->user()->id;
        $charge = $this->chargeService->getChargeToUserHistory();
        return Common::apiResponse(1, '', $charge, 200);
    }
    

    public function chargeToAgencyHistory($request){
        
        $charge = $this->chargeService->getChargeAgencyHistory();
        return Common::apiResponse(1, '', $charge, 200);
  
    }


}
