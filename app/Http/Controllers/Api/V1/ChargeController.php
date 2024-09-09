<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ChargeRecievedInfoResource;
use Exception;
use App\Models\User;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Tik\Services\ChargeRepoService;
use App\Http\Resources\Api\V1\TrxResource;
use App\Http\Resources\Api\V1\ChargeResource;
use App\Http\Resources\Api\V1\ChargeResourceforAgencyCharge;
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
        $user      = $request->user();
        $count     = $request->amount;
        $userUuid = $request->id;
        if ($user->charge_status == 0) {
            return Common::apiResponse(0, __('api.freez_charge'), 404);
        }
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

        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        if ($stop_all_charge == 1) {
            return Common::apiResponse(0, __('api_responses.freez_charge'), 404);
        }
        $toId = $request->to_id;
        $from = $request->user();
        $isRoomTarget = false;
        $to   = User::withoutAppends()->searchByUuid($toId)->first();


        if ($from->charge_status == 0) {
            return Common::apiResponse(0, __('api.freez_charge'), 404);
        }
        $usd  = $request->usd;

        if ($usd < 0 || !is_numeric($usd)) {
            return Common::apiResponse(0, 'this value not allow', 422);
        }

        if (!$usd || !$to) {
            return Common::apiResponse(0, 'not found', 404);
        }
        $rate = Common::getConf('one_usd_value_in_coins');
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
            $data = [
                'coins' => $from->di,
                'usd' => $from->salary,
            ];

            DB::commit();
            return Common::apiResponse(1, 'success',  $data, 201);
        } catch (Exception $exception) {
            // Log::info('this from charge to - ' . $exception->getMessage());
            DB::rollBack();
            return Common::apiResponse(0, $exception->getMessage(), 400);
        }
    }


    public function sendMoneyFoeHost(Request $request)
    {

        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        if ($stop_all_charge == 1) {
            return Common::apiResponse(0, __('api.freez_charge'), 404);
        }

        $user      = $request->user();

        if ($user->charge_status == 0) {
            return Common::apiResponse(0, __('api.freez_charge'), 404);
        }
        $count     = $request->amount;
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
            // Increment recipient's coins
            UserCommon::UserEarnedInvitation($userReceiver->id, $count);
            $data = [
                'coins' => $user->di,
                'usd' => $user->salary,
            ];
            return Common::apiResponse(1, 'your recharge was successful', $data, 200);
        } catch (Exception $e) {
            return Common::apiResponse(0,  $e->getMessage());
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
        //        return Common::apiResponse(0, 'try again');
        $user      = $request->user();
        $count     = $request->amount;
        $userUuid = $request->id;
        if ($user->charge_status == 0) {
            return Common::apiResponse(0, __('api.freez_charge'), 404);
        }
        if ($count < 0 || !is_numeric($count)) {
            return Common::apiResponse(0, 'this value not allow', 422);
        }
        if (!$userUuid || !$count) {
            return Common::apiResponse(0, __('api_responses.missing_params'), 404);
        }

        try {
            [$receiver, $amount]  = $this->chargeService->chargeDollarForOwner($user, $userUuid, $count);
            DB::commit();
            if ($user instanceof User) {
                (new UserAchievementService())->insertCharging($receiver, $amount);
            }
            UserCommon::UserEarnedInvitation($receiver->id, $amount);
            $data = [
                'coins' => $user->di,
                'usd' => $user->salary,
            ];
            return Common::apiResponse(1, 'Your recharge was successful', $data, 200);
        } catch (Exception $e) {
            DB::rollBack();
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
        $charge = $this->chargeService->getChargeUserHistory($userId, 'received', 'Host agent');

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
}
