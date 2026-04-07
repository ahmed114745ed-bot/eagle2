<?php

namespace App\Http\Controllers\utd;

use Exception;
use App\Models\User;
use App\Models\Agency;
use App\Models\Charge;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ChargeResource;
use App\Http\Resources\UserChargeResource;
use Modules\Achievement\Http\Services\UserAchievementService;

class ChargesController extends Controller
{
    public function index()
    {

        $from = request('from');
        $to = request('to');
        $sort = request('sort');
        $user_type = request('user_type');
        $perPage = request('per_page') ?? 10;

        $charges = Charge::with('user.profile')->when($from && $to, function ($q) use ($from, $to) {
            $q->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to);
        })
            ->when($user_type, function ($q) use ($user_type) {
                $q->where('user_type', $user_type);
            })
            ->when($sort, function ($q) use ($sort) {
                $q->orderBy('id', $sort);
            })
            ->paginate($perPage);

        return Common::apiResponse(true, 'Success', ChargeResource::collection($charges));
    }

    public function store(Request $request)
    {
        if ($request->user_type != 'dash') {
            $user = $this->getUser($request);
            if (!$user) {
                return Common::apiResponse(false, 'user not found');
            }

            if ($this->isInvalidAmount($request->amount)) {
                return Common::apiResponse(false, 'amount must be more than 10');
            }
        }

        if ($request->user_type == 'dash') {
            $agency = $this->getAgency($request->user_id);
            if (!$agency) {
                return Common::apiResponse(false, __('api_responses.agency'));
            }
            $user = $agency->owner;
            return $this->handleAgencyCharge($request, $agency, $user);
        }

        return $this->handleUserCharge($request, $user);
    }


    private function getUser(Request $request)
    {
        if ($request->user_type == 'dashdash') {
            return $request->id_type == '1'
                ? User::query()->searchByUuid($request->user_id)->first()
                : User::query()->find($request->user_id);
        }

        return $request->id_type == '1'
            ? User::query()->where('uuid', $request->user_id)->first()
            : User::query()->find($request->user_id);
    }

    private function getAgency($agencyId)
    {
        return Agency::where("id", $agencyId)->first();
    }

    private function isInvalidAmount($amount)
    {
        return $amount < 10;
    }


    private function handleAgencyCharge(Request $request, Agency $agency, User $user)
    {
        $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;
        if ($amount < 0 && $agency->coins < abs($amount)) {
            return Common::apiResponse(false, __('Insufficient agency balance'));
        }

        $appBaseRate = \App\Services\CoinRateService::getAppBaseRate();
        $calc = \App\Services\ChargeCalculationService::calculate((float)$amount, 'coins', $appBaseRate);

        $chargeService = app(\App\Tik\Services\ChargeRepoService::class);
        $chargeService->chargeAgencyNew($agency->id, $amount, 'dash', $calc);

        if ($request->charge_type == "increment") {
            $admin = Auth::user()->username ?? 'Admin';
            CustomNotification::chargeAction($user, $request, $admin);
        }

        return Common::apiResponse(true, 'Success');
    }


    private function handleUserCharge(Request $request, User $user)
    {
        $appBaseRate = \App\Services\CoinRateService::getAppBaseRate();
        $calc = \App\Services\ChargeCalculationService::calculate((float)$request->amount, 'coins', $appBaseRate);

        $chargeService = app(\App\Tik\Services\ChargeRepoService::class);
        $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;
        
        $chargeService->chargeTo(Auth::id() ?? $request->charger_id, $user->id, $amount, 'app', 'dash', $calc);
        (new UserAchievementService())->insertCharging($user, $request->amount);

        return Common::apiResponse(true, 'Success');
    }


    public function userCharge($id, Request $request)
    {
        try {
            $data =   Charge::where('user_id', $id)->with('sender')->paginate($request->perPage, ['*'], 'page', $request->page);
            return Common::apiResponse(true, 'done',  UserChargeResource::collection($data));
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function userMonthCharge($id, Request $request)
    {
        try {
            $year = $request->year ?? date('y');
            $data = Charge::where('user_id', $id)->whereYear('created_at', $year)
                ->selectRaw('MONTH(created_at) as month, SUM(COALESCE(total_coins, amount)) as total_amount')
                ->groupBy('month')->orderBy('month')->get();
            return Common::apiResponse(true, 'done',  $data);
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
