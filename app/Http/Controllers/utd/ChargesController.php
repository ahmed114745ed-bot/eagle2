<?php

namespace App\Http\Controllers\utd;

use App\Facades\CustomNotification;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChargeResource;
use App\Models\Agency;
use App\Models\Charge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Achievement\Http\Services\UserAchievementService;

class ChargesController extends Controller
{
    public function index(){

        $from = request('from');
        $to = request('to');

        $user_type = request('user_type');

        $charges = Charge::with('user.profile')->when($from && $to , function($q)use($from, $to){
            $q->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);
        })
        ->when($user_type,function($q)use($user_type){
            $q->where('user_type', $user_type);
        })
        ->paginate(10);

        return Common::apiResponse(true, 'Success', ChargeResource::collection($charges));
    }

    public function store(Request $request){
        if ($request->user_type != 'dash') {
            $user = $this->getUser($request);
            if (!$user) {
                return Common::apiResponse(false,'user not found');
            }

            if ($this->isInvalidAmount($request->amount)) {
                return Common::apiResponse(false,'amount must be more than 10');
            }
        }

        if ($request->user_type == 'dash') {
            $agency = $this->getAgency($request->user_id);
            if (!$agency) {
                return Common::apiResponse(false,__('api_responses.agency'));
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
            return Common::apiResponse(false,__('Insufficient agency balance'));
        }

        DB::transaction(function () use ($request, $agency, $user, $amount) {
            $agency->coins += $amount;
            $agency->save();

            $this->createChargeRecord($request, $user, $agency, $amount);

            if ($request->charge_type == "increment") {
                CustomNotification::chargeAction($user, $request);
            }
        });

        return Common::apiResponse(true, 'Success');
    }


    private function handleUserCharge(Request $request, User $user)
    {
        $percentage = Common::getConf("special_transfer_to_usd") ?? 1;
        $usdAmount = $request->amount / $percentage;

        DB::transaction(function () use ($request, $user, $usdAmount) {
            $amount = $request->charge_type == 'increment' ? $request->amount : -$request->amount;
            if ($amount < 0 && $user->di < abs($amount)) {
                return Common::apiResponse(false,__('Insufficient user balance'));
            }

            $user->di += $amount;
            $user->save();

            $this->createChargeRecord($request, $user, null, $amount, $usdAmount);
            (new UserAchievementService())->insertCharging($user, $request->amount);
        });

        return Common::apiResponse(true, 'Success');
    }

    private function createChargeRecord(Request $request, User $user, ?Agency $agency, $amount, $usdAmount = 0)
    {
        $charge = new Charge();
        $charge->charger_id = Auth::id() ?? $request->charger_id;
        $charge->charger_type = $request->user_type == 'dash' ? 'dash' : 'dash';
        $charge->user_id = $user->id;
        $charge->agency_id = $agency->id ?? null;
        $charge->user_type = $request->user_type ?? 'app';
        $charge->amount = $amount;
        $charge->usd = $usdAmount;
        $charge->balance_before = ($agency ? $agency->coins : $user->di) - $amount;
        //dd($charge);
        $charge->save();

        UserCommon::UserEarnedInvitation($user->id,$amount);

    }
}
