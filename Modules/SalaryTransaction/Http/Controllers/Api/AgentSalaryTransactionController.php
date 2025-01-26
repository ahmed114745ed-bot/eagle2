<?php

namespace Modules\SalaryTransaction\Http\Controllers\Api;

use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Http\Resources\Api\V1\ChargeAgentResource;
use App\Models\PaymentGateway;
use App\Tik\Repositories\UserRepository;
use Modules\SalaryTransaction\Helpers\TransactionCustomNotification;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ChargeResourceforAgencyCharge;
use App\Models\Agency;
use App\Models\AgencySallary;
use App\Models\Charge;
use App\Models\Config;
use App\Models\User;
use App\Models\UserSallary;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\SalaryTransaction\Entities\AdminCheck;
use Modules\SalaryTransaction\Entities\AgencyTransferSalary;
use Modules\SalaryTransaction\Entities\AgentSalaryRequest;
use Modules\SalaryTransaction\Entities\ChargeCountry;
use Modules\SalaryTransaction\Transformers\RequestsResource;
use Modules\SalaryTransaction\Entities\PendingSalaryRequest;
use Modules\SalaryTransaction\Entities\SalaryRequest;
use Modules\SalaryTransaction\Transformers\ChargeAgentResource as TransformersChargeAgentResource;
use Modules\SalaryTransaction\Transformers\ChargeCountryResource;
use Modules\SalaryTransaction\Transformers\HostRequestsResource;

class AgentSalaryTransactionController extends Controller
{

    public function __construct(private readonly UserRepository         $userRepository,)
    {


    }

    public function charge_co_for_usersHistory(Request $request)
    {
        $me = $request->user();


        $agency = $me->ownAgency;
        if (!$agency) {
            return Common::apiResponse(0, __("api_responses.agency"));
        }
        $q = Charge::query()->with([
            'sender'      => function ($query) {
                $query->withoutAppends();
            }, 'receiver' => function ($query) {
                $query->withoutAppends();
            }
        ])->where('is_used_transferred', false)->where("agency_id",$agency->id);

        if ($request->type == 'received') {
            $q = $q->where("user_id",$me->id)->where('agency_id', $me->agency_id);
        }
        if ($request->type == 'sent') {
            $q = $q->where("charger_id",$me->id)->where('agency_id', $me->agency_id)->where('charger_type', '!=', 'dash');
        }

        return Common::apiResponse(1, '', ChargeResourceforAgencyCharge::collection($q->paginate()), 200);
    }

    public function send_money_for_the_host(Request $request)
    {
        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        if ($stop_all_charge == 1) {
            return Common::apiResponse(0, __('api_responses.freez_charge'), 404);
        }

        $user      = $request->user();
        if ($user->charge_status == 0) {
            return Common::apiResponse(0, __('api_responses.freez_charge'), 404);
        }

        $count     = $request->amount;
        $user_uuid = $request->user_id;
        if ($count < 0) {
            return Common::apiResponse(0, 'this value not allow', 422);
        }
        $user_Resve = $this->userRepository->searchUser($user_uuid);
        if (!$user_Resve) {
            return Common::apiResponse(0, __('api_responses.this_user_not_found'));
        }

        if ($user_Resve ->id ==  $user->id) {
            return Common::apiResponse(0, __('salaryTransaction::api_responses.not_this_user'));
        }
        $user_id = $user_Resve->id;
        $agency = $user->agency;
        if (!$agency) {
            return Common::apiResponse(0, __('api_responses.agency'));
        }
        if ($agency->coins < $count) {
            return Common::apiResponse(0, __('api_responses.balance_not_enough'));
        }
        $user_type = User::find($user_id);


        try {
            // Decrement sender's coins
            $agency->decrement('coins', $count);


            User::where('id', $user_id)->increment('di', $count);
            $type         = 0;

            $userTypes = [
                0 => 'user',
                1 => 'host',
                2 => 'Host agent',
                3 => 'freight forwarder',
                4 => 'freight forwarder and Host agent',
                5 => 'Administrative',
            ];
            if (is_object($user_type)) {
                $type = $userTypes[intval($user_type->type_user)] ?? 'user';
            } else {
                // Handle the case when $user_type is not an object
                $type = 'user'; // or any other default value you prefer
            }


            $charge = Charge::query()->create([
                'charger_id'  => $user->id, 'charger_type' => 'freight forwarder',
                'user_id'     => $user_id, 'user_type' => $type, 'amount' => $count,
                'amount_type' => 2, 'agency_id' => $agency->id
                // 'balance_before'=>$user_id->di
            ]);
            // Increment recipient's coins
            UserCommon::UserEarnedInvitation($user_id, $count);

            return Common::apiResponse(1, __('api_responses.your_recharge_was_successful'), [
                'transfer_amount' => $charge->amount,
                'operation_number' => $charge->id,
                'date' => Carbon::parse($charge->created_at)->toDateTimeString()
            ]);
        } catch (Exception $e) {
            // If an error occurs during the update process
            echo $e->getMessage();

            return Common::apiResponse(0, __('api_responses.an_error_occurred_please_try_again_later'));
        }
    }

    public function searchAgent(Request $request)
    {
        $countryId = $request->country_id;
        $paymentId = $request->payment_id;

        $agencies = Agency::with("Countries","AgencypaymentGateways")->has("chargeAgency")->withCount(['salaryRequests' => function($query) {
            $query->where('status', 3);
        }])->whereHas('owner')
        ->when($countryId,fn($q)=>$q->whereHas('Countries', fn($q) => $q->where('country_id', $countryId)))
        ->when($paymentId,fn($q)=>$q->whereHas('AgencypaymentGateways',  fn($q) => $q->where('payment_gateway_id', $paymentId)))
        ->paginate(15);
        return Common::apiResponse(true, 'agencies',TransformersChargeAgentResource::collection($agencies));
    }
    public function add_request_salary(Request $request)
    {
        try {
            if(!$request->amount)
            {
                return Common::apiResponse(0, __('salaryTransaction::api_responses.missing_params'), null, 422);
            }

            $agent = $request->user();
            $agency = $agent->ownAgency;
            $usd =$request->amount;


            if ($agency->transfer_salary < $usd) {
                return Common::apiResponse(0, __('api_responses.balance_not_enough'));
            }

            $coins = Config::where("name","one_usd_value_in_coins")->first();
            $coin_usd = $usd * $coins->value ?? 0;

            AgentSalaryRequest::create([
                "agency_id"             => $agency->id,
                "agency_owner_id"       => $agent->id,
                "status"                => 0,
                "type"                  => $request->type,
                "payment_gateway_id"    => $request->payment_gateway_id ?? 0,
                "country_id"            => $request->country_id ?? 0,
                "coins"                 => $coin_usd,
                "usd"                   => $usd,
            ]);
            $this->updatesalaryTransfer($agency->id , $usd);
            // $this->updateAgencySalary($agency->id , $request->usd );

            // TransactionCustomNotification::sendRequest($agency_owner->id);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        return Common::apiResponse(1, __('salaryTransaction::api_responses.request_added_success'));
    }

    public function updateAgencySalary($agentId,$amount)
    {
        $userSalary = AgencySallary::firstOrNew([
            "agency_id" => $agentId,
            "month" => date("m"),
            "year" => date("Y"),
        ]);

        if ($userSalary->exists) {
            $userSalary->increment('cut_amount', $amount);
        } else {
            $userSalary->cut_amount = $amount;
            $userSalary->save();
        }
    }

    public function updatesalaryTransfer($agencyId, $usd)
    {
         AgencyTransferSalary::updateOrCreate([
            'agency_id' => $agencyId,
            "month" => date("m"),
            "year" => date("Y"),
        ],[
            'pending_usd' => DB::raw('pending_usd + ' . $usd),
        ]);
    }

    public function charge_country()
    {
        $resulty = ChargeCountry::with("country")->get();

        return Common::apiResponse(1, '', ChargeCountryResource::collection($resulty));
    }

    public function shipping_agencies(Request $request)
    {
        $countryId = $request->country_id;
        $paymentId = $request->payment_id;

        $agencies = Agency::with("Countries","AgencypaymentGateways")->has("chargeAgency")->withCount(['salaryRequests' => function($query) {
            $query->where('status', 3);
        }])->whereHas('owner')
        ->where('Shipping_agency',true)
        ->when($countryId,fn($q)=>$q->whereHas('Countries', fn($q) => $q->where('country_id', $countryId)))
        ->when($paymentId,fn($q)=>$q->whereHas('AgencypaymentGateways',  fn($q) => $q->where('payment_gateway_id', $paymentId)))
        ->paginate(15);
        return Common::apiResponse(true, 'agencies',TransformersChargeAgentResource::collection($agencies));
    }
    
}
