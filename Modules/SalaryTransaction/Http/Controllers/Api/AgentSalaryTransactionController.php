<?php

namespace Modules\SalaryTransaction\Http\Controllers\Api;

use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Http\Resources\Api\V1\ChargeAgentResource;
use App\Models\ShippingAgency;
use App\Tik\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ChargeResourceforAgencyCharge;
use App\Models\Agency;
use App\Models\AgencySallary;
use App\Models\Charge;
use App\Models\Config;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\SalaryTransaction\Entities\AgencyTransferSalary;
use Modules\SalaryTransaction\Entities\AgentSalaryRequest;
use Modules\SalaryTransaction\Entities\ChargeCountry;
use Modules\SalaryTransaction\Transformers\ChargeAgentResource as TransformersChargeAgentResource;
use Modules\SalaryTransaction\Transformers\ChargeCountryResource;

class AgentSalaryTransactionController extends Controller
{

    public function __construct(private readonly UserRepository         $userRepository,) {}

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
            },
            'receiver' => function ($query) {
                $query->withoutAppends();
            }
        ])->where('is_used_transferred', false)->where("agency_id", $agency->id);

        if ($request->type == 'received') {
            $q = $q->where("user_id", $me->id)->where('agency_id', $me->agency_id);
        }
        if ($request->type == 'sent') {
            $q = $q->where("charger_id", $me->id)->where('agency_id', $me->agency_id)->where('charger_type', '!=', 'dash');
        }

        return Common::apiResponse(1, '', ChargeResourceforAgencyCharge::collection($q->paginate()), 200);
    }

    public function chargeCoForUserHistory(Request $request)
    {
        $usrAuth = $request->user();
        $agency = $usrAuth->shippingAgency;
        $search = $request->search;
        $type = $request->type ?? null;
        if (!$type) {
            return Common::apiResponse(0, __("type not found"));
        }
        if (!$agency) {
            return Common::apiResponse(0, __("api_responses.agency"));
        }
        $data = Charge::query();
                        //  where('is_used_transferred', false)
                        // ->where("charger_type", 'agency')
                        // ->where("charger_id", $agency->id);

        $data = $data->when($type == 'sent', function ($q) use ($search, $agency) {
            $q->where("charger_id", $agency->id)
              ->where('charger_type',  'agency')

              ->with('receiverUser','receiveragency');

        })
        ->when($type == 'received', function ($q) use ($search, $agency) {
            $q->where('user_id', $agency->id)->where('user_type','agency')
            //   ->whereHas('sender', function ($q2) use ($search) {
            //       $q2->fitterByUuid($search);
            //   });
            ->with('senderUser','senderShippingAgency','senderAgency','admin','areaManager','subAreaManager','superAdmin','subSuperAdmin','bd');

        })->orderByDesc('id')->paginate();

        return Common::apiResponse(1, '', ChargeResourceforAgencyCharge::collection($data), 200);
    }
    public function chargeDollarForUserHistory(Request $request)
    {
        $usrAuth = $request->user();
        $agency = $usrAuth->agency;
        $search = $request->search;
        $type = $request->type ?? null;
        if (!$type) {
            return Common::apiResponse(0, __("type not found"));
        }
        if (!$agency) {
            return Common::apiResponse(0, __("api_responses.agency"));
        }

        $data = Charge::query();

        $data = $data->when($type == 'sent', function ($q) use ($search, $agency) {
            $q->where("charger_id", $agency->id)
              ->where('charger_type',  'host_agency')
              ->with(Common::chargerRelationsQuery());
            //   ->with('receiverUser','receiveragency');

        })
        ->when($type == 'received', function ($q) use ($search, $agency) {
            $q->where('user_id', $agency->id)->where('user_type','agency')
            ->with(Common::chargerRelationsQuery());
            // ->with('senderUser','senderShippingAgency','senderAgency','admin');

        })->orderByDesc('id')->paginate();





        return Common::apiResponse(1, '', ChargeResourceforAgencyCharge::collection($data), 200);
    }

    public function send_money_for_the_host(Request $request)
    {
        //done
        $stop_all_charge = settings()->get("stop_charge") ? settings()->get("stop_charge") : 0;
        if ($stop_all_charge == 1) {
            return Common::apiResponse(0, __('api_responses.freez_charge'), 404);
        }

        $user      = $request->user();
//        if ($user->charge_status == 0) {
//            return Common::apiResponse(0, __('api_responses.'), 404);
//        }

        $count     = $request->amount;
        $user_uuid = $request->user_id;
        if ($count < 0) {
            return Common::apiResponse(0, 'this value not allow', 422);
        }
        $user_Resve = $this->userRepository->searchUser($user_uuid);
        if (!$user_Resve) {
            return Common::apiResponse(0, __('api_responses.this_user_not_found'));
        }


        $user_id = $user_Resve->id;
        $agency = $user->shippingAgency;
        if (!$agency) {
            return Common::apiResponse(0, __('api_responses.agency'));
        }
        if ($agency->coins < $count) {
            return Common::apiResponse(0, __('api_responses.balance_not_enough'));
        }
        if ($agency->is_frozen == 1) {
            return Common::apiResponse(0, __('api_responses.frozen_agency'));
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


            $shippingCoins = \Cache::rememberForever('shipping_coins', function () {
                $setting = \App\Models\Setting::where('key', 'shipping_coins')->first();
                return $setting?->value ?? 1;
            });
            $usdAmount = $shippingCoins > 0 ? $count / $shippingCoins : 0;

            $charge = Charge::query()->create([
                'charger_id'  => $user->id,
                'charger_type' => 'agency',
                'user_id'     => $user_id,
                'user_type' => $type,
                'amount' => $count,
                'usd' => $usdAmount,
                'amount_type' => 2,
                'agency_id' => $agency->id
                // 'balance_before'=>$user_id->di
            ]);
            // Increment recipient's coins
            UserCommon::UserEarnedInvitation($user_id, $count,$charge->id);

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

    public function searchAgent(Request $request): JsonResponse
    {
        $countryId = $request->country_id;
        $paymentId = $request->payment_id;

        $agencies = ShippingAgency::with("Countries", "AgencypaymentGateways")->withCount('senderCharges')->whereHas('owner', fn($query) => $query->where('appear_charger_agency', 1))->with('owner')
            ->when($countryId, fn($q) => $q->whereHas('Countries', fn($q) => $q->where('country_id', $countryId)))
            ->when($paymentId, fn($q) => $q->whereHas('AgencypaymentGateways',  fn($q) => $q->where('payment_gateway_id', $paymentId)))
            ->orderByDesc('sender_charges_count')
            ->paginate(10);
        return Common::apiResponse(true, 'agencies', TransformersChargeAgentResource::collection($agencies));
    }

    public function searchAgentV2(Request $request): JsonResponse
    {
        $countryId = $request->country_id;
        $paymentId = $request->payment_id;

        $agencies = ShippingAgency::with("Countries", "AgencypaymentGateways")
            ->withCount('senderCharges')
            ->whereHas('owner', fn($query) => $query->where('appear_charger_agency', 1))
            ->with('owner')
            ->when($countryId, fn($q) => $q->whereHas('Countries', fn($q) => $q->where('country_id', $countryId)))
            ->when($paymentId, fn($q) => $q->whereHas('AgencypaymentGateways',  fn($q) => $q->where('payment_gateway_id', $paymentId)))
            ->paginate(10);
        return Common::apiResponse(true, 'agencies', TransformersChargeAgentResource::collection($agencies));
    }
    public function add_request_salary(Request $request)
    {
        try {
            if (!$request->amount) {
                return Common::apiResponse(0, __('salaryTransaction::api_responses.missing_params'), null, 422);
            }

            $agent = $request->user();
            $agency = $agent->ownAgency;
            $usd = (float) $request->amount;


            if ($agency->transfer_salary < $usd) {
                return Common::apiResponse(0, __('api_responses.balance_not_enough'));
            }

            $appBaseRate = \App\Services\CoinRateService::getAppBaseRate();
            $calc = \App\Services\ChargeCalculationService::calculate($usd, 'usd', $appBaseRate);

            AgentSalaryRequest::create([
                "agency_id"             => $agency->id,
                "agency_owner_id"       => $agent->id,
                "status"                => 0,
                "type"                  => $request->type,
                "payment_gateway_id"    => $request->payment_gateway_id ?? 0,
                "country_id"            => $request->country_id ?? 0,
                "coins"                 => $calc['total_coins'],
                "usd"                   => $calc['base_usd'],
                "applied_coin_rate"     => $calc['applied_coin_rate'],
                "base_coins"            => $calc['base_coins'],
                "bonus_coins"           => $calc['bonus_coins'],
                "profit_usd"            => $calc['profit_usd'],
                "profit_coins"          => $calc['profit_coins'],
                "total_coins"           => $calc['total_coins'],
                "rate_source"           => 'app'
            ]);
            $this->updatesalaryTransfer($agency->id, $usd);
            // $this->updateAgencySalary($agency->id , $request->usd );

            // TransactionCustomNotification::sendRequest($agency_owner->id);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        return Common::apiResponse(1, __('salaryTransaction::api_responses.request_added_success'));
    }

    public function updateAgencySalary($agentId, $amount)
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
        ], [
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

        $agencies = Agency::with("Countries", "AgencypaymentGateways")
        ->withCount('receiveShippingAgencyCharges')
        ->withCount(['salaryRequests' => function ($query) {
            $query->where('status', 3);
        }])->whereHas('owner')
           ->whereHas('hasShippingAgency')
            // ->where('Shipping_agency', true)
            ->when($countryId, fn($q) => $q->whereHas('Countries', fn($q) => $q->where('country_id', $countryId)))
            ->when($paymentId, fn($q) => $q->whereHas('AgencypaymentGateways',  fn($q) => $q->where('payment_gateway_id', $paymentId)))
            ->paginate(10);
        return Common::apiResponse(true, 'agencies', TransformersChargeAgentResource::collection($agencies));
    }
}
