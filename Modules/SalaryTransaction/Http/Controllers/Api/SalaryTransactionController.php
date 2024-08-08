<?php

namespace Modules\SalaryTransaction\Http\Controllers\Api;

use App\Helpers\Common;
use App\Models\PaymentGateway;
use Modules\SalaryTransaction\Helpers\TransactionCustomNotification;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AgencySallary;
use App\Models\Config;
use App\Models\User;
use App\Models\UserSallary;
use Auth;
use Carbon\Carbon;
use Google\Service\CloudWorkstations\Host;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\SalaryTransaction\Entities\AdminCheck;
use Modules\SalaryTransaction\Entities\AgencyTransferSalary;
use Modules\SalaryTransaction\Transformers\RequestsResource;
use Modules\SalaryTransaction\Entities\PendingSalaryRequest;
use Modules\SalaryTransaction\Entities\SalaryRequest;
use Modules\SalaryTransaction\Transformers\HostRequestsResource;

class SalaryTransactionController extends Controller
{
    public function add_request_salary(Request $request)
    {
        try {
            if(!$request->agent_id || !$request->payment_gateway_id || !$request->country_id || !$request->usd)
            {
                return Common::apiResponse(0, __('api_responses.missing_params'), null, 422);
            }
            if ($request->usd < 0 || (is_numeric($request->usd) && strpos($request->usd, '.') !== false)){
                return Common::apiResponse(0, __('api_responses.missing_params'), null, 422);
            }

            if (!PaymentGateway::find($request->payment_gateway_id)){
                return Common::apiResponse(0, __('salaryTransaction::api_responses.un_supported_payment_gateway'), null, 422);
            }
            $host = $request->user();
            if ($host->salary < $request->usd) {
                return Common::apiResponse(0, __('salaryTransaction::api_responses.dont_have_coin'), null, 422);
            }

            $agency = Agency::with("owner")->where("app_owner_id",$request->agent_id)->first();
            if (!$agency instanceof Agency) return Common::apiResponse(false, 'agency not found');
            if ($agency->Shipping_agency != 1 ) {
                return Common::apiResponse(0, __('api_responses.agency_not_shipping'), null, 422);
            }
            $agency_owner = $agency->owner;
            $percentage_value = Common::getConfig('one_usd_value_in_coins')  ?? 10;
            $coin_usd = $request->usd * $percentage_value;

            $check_requests = SalaryRequest::where('host_id',$host->id)->whereIn("status",[0,1,2])->first();
            if ($check_requests != null) {
                return Common::apiResponse(0, __('salaryTransaction::api_responses.have_request_before'), null, 422);
            }

            SalaryRequest::create([
                "agency_id"             => $agency->id,
                "agency_owner_id"       => $agency_owner->id,
                "host_id"               => $host->id,
                "status"                => 0,
                "payment_gateway_id"    => $request->payment_gateway_id,
                "country_id"            => $request->country_id,
                "coins"                 => $coin_usd,
                "usd"                   => $request->usd,
                "note"                  => $request->note,
            ]);

            $this->updateUserSalary($host , $request->usd );

            TransactionCustomNotification::sendRequest($agency_owner->id);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        return Common::apiResponse(1, __('salaryTransaction::api_responses.request_added_success'), null);
    }

    public function updateUserSalary(User $host,$amount)
    {

        PendingSalaryRequest::create([
            "user_id" => $host->id,
            "salary" => $amount,
            "status" => 0,
            "type" => 'salary_transaction',
        ]);

        $userSalary = UserSallary::where([ "user_id" => $host->id,"month" => date("m"),"year" => date("Y")]);
        if ($host->agency_id != null && $host->agency_id != 0) {
            $userSalary = $userSalary->where("user_agency_id",$host->agency_id);
        }else{
            $userSalary = $userSalary->orderByDesc("id");
        }
        $userSalary = $userSalary->first();

        if ($userSalary != null) {
            $userSalary->cut_amount += $amount;
            $userSalary->save();
        }else{
            UserSallary::create([
                "user_id" => $host->id,"month" => date("m"),"year" => date("Y") ,'cut_amount'=>$amount
            ]);
        }
    }

    public function get_requests()
    {
        $user = Auth::user();
        $type = request("type") ?? 0;
        $requests = SalaryRequest::whereHas("agency",function($q) use ($user){
            $q->where("app_owner_id",$user->id);
        })->where('status',$type)->paginate();

        $result = RequestsResource::collection($requests);
        return Common::apiResponse(1, '', $result);
    }

    public function action_request(Request $request)
    {
        if (!$request->request_id) {
            return Common::apiResponse(0, __('salaryTransaction::api_responses.missing_params'), null, 422);
        }
        if ($request->answer != 0 && $request->answer != 1) {
            return Common::apiResponse(0, __('salaryTransaction::api_responses.correct_data'), null, 422);
        }
        $user = $request->user();
        try {
            $requestSalary = SalaryRequest::with("host")->findOrFail($request->request_id);
        } catch (\Exception $e) {

            ///todo translate
            return Common::apiResponse(0, __('salaryTransaction::api_responses.request_not_found'), null, 422);
        }
        if ($requestSalary->status != 0) {
            return Common::apiResponse(0, __('salaryTransaction::api_responses.correct_data'), null, 422);
        }
        $host = $requestSalary->host;
        if (Carbon::parse($requestSalary->created_at)->addHours(48)->isPast()) {
            $requestSalary->status = 4;
            $requestSalary->save();
            $this->updateHostDi($host ,$requestSalary->usd);
            TransactionCustomNotification::action_request($host->id, 0,$requestSalary->coins);
            return Common::apiResponse(0, __('salaryTransaction::api_responses.request_expired'), null, 422);
        }
        if ($request->answer == 0) {
            $this->updateHostDi($host ,$requestSalary->usd);
            PendingSalaryRequest::where([
                "user_id" => $host->id,
                "type" => 'salary_transaction',
            ])->delete();
            TransactionCustomNotification::action_request($host->id, 5,$requestSalary->usd);
        }
       
        try {
            $requestSalery = SalaryRequest::with("host")->findOrFail($request->request_id);
        } catch (\Exception $e) {

            ///todo translate
            return Common::apiResponse(0, __('salaryTransaction::api_responses.request_not_found'), null, 422);
        }
        if ($requestSalery->agency?->app_owner_id != $user->id) {
            return Common::apiResponse(0, __('api_responses.not_agency_owner'), null, 422);
        }
        if ($requestSalery->status != 0) {
            return Common::apiResponse(0, __('api_responses.correct_data'), null, 422);
        }
       
        
       $requestSalary->update([
            'status' => $request->answer == 0 ? 4 : 1
        ]);

        return Common::apiResponse(1,__("salarytransaction::api_responses.edit_in_request"), []);
    }

    public function updateHostDi(User $host,$coins)
    {
        // $userSalary = UserSallary::updateOrCreate([
        //     "user_id" => $host->id,
        //     "month" => date("m"),
        //     "year" => date ('Y'),
        // ],[
        //     "cut_amount" => DB::raw("cut_amount - " . $coins)
        // ]);
        $userSalary = UserSallary::where([ "user_id" => $host->id,"month" => date("m"),"year" => date("Y")]);
        if ($host->agency_id != null && $host->agency_id != 0) {
            $userSalary = $userSalary->where("user_agency_id",$host->agency_id);
        }else{
            $userSalary = $userSalary->orderByDesc("id");
        }
        $userSalary = $userSalary->first();

        if ($userSalary != null) {
            $userSalary->cut_amount -= $coins;
            $userSalary->save();
        }else{
            UserSallary::create([
                "user_id" => $host->id,"month" => date("m"),"year" => date("Y") ,'cut_amount'=> -$coins
            ]);
        }
        
    }

    public function transfer_salary(Request $request)
    {
        if (!$request->request_id || !$request->bill_image) {
            return Common::apiResponse(0, __('salaryTransaction::api_responses.missing_params'), null, 422);
        }
        $user = $request->user();
        
        try {
            $requestSalary = SalaryRequest::with("host")->findOrFail($request->request_id);
        } catch (\Exception $e) {

            ///todo translate
            return Common::apiResponse(0, __('salaryTransaction::api_responses.request_not_found'), null, 422);
        }
        if ($requestSalary->agency?->app_owner_id != $user->id) {
            return Common::apiResponse(0, __('api_responses.not_agency_owner'), null, 422);
        }
        if ($requestSalary->status != 1 ) {
            return Common::apiResponse(0, __('salaryTransaction::api_responses.correct_data'), null, 422);
        }
        if ($request->hasFile('bill_image')) {
            $invalidImage = Common::upload('images', $request->file('bill_image'));
            $requestSalary->bill_image = $invalidImage;
        }
        $requestSalary->status = 2;
        $requestSalary->save();
        TransactionCustomNotification::action_request($requestSalary->host_id, 3,$requestSalary->usd);
        return Common::apiResponse(1,__("salarytransaction::api_responses.edit_in_request"), []);
    }

    public function host_requests(Request $request)
    {
        $user = Auth::user();
        $type = request("type") ?? 0;

        $requests = SalaryRequest::where("host_id",$user->id)->where('status',$type)->paginate();

        $result = HostRequestsResource::collection($requests);
        return Common::apiResponse(1, '', $result);
    }

    public function host_action(Request $request)
    {
        if (!$request->request_id || !$request->answer) {
            return Common::apiResponse(0, __('salaryTransaction::api_responses.missing_params'), null, 422);
        }
        $user = $request->user();
       
        try {
            $requestSalary = SalaryRequest::with("host")->findOrFail($request->request_id);
        } catch (\Exception $e) {

            ///todo translate
            return Common::apiResponse(0, __('salaryTransaction::api_responses.request_not_found'), null, 422);
        }
        if ($requestSalary->host_id != $user->id) {
            return Common::apiResponse(0, __('api_responses.not_agency_owner'), null, 422);
        }
        if ($requestSalary->status != 2 ) {
            return Common::apiResponse(0, __('salaryTransaction::api_responses.correct_data'), null, 422);
        }

        $agency_owner = $requestSalary->agency?->owner;
        $agency = $requestSalary->agency;
        if ($agency->Shipping_agency != 1 ) {
            return Common::apiResponse(0, __('api_responses.agency_not_shipping'), null, 422);
        }
        $host = $request->user();
        if ($request->answer == "confirm") {
            // update status
            $requestSalary->status = 3;
            $requestSalary->host_check =1;
            // $requestSalary->request_admin_status =1;
            //delete user pending salary
            PendingSalaryRequest::where([
                "user_id" => $host->id,
                "type" => 'salary_transaction',
            ])->delete();

            $this->updatesalaryTransfer($agency->id,$requestSalary->usd);
            // add salary to agency
            // AgencySallary::updateOrCreate([
            //     'agency_id' => $agency->id,
            //     "month" => date("m"),
            //     "year" => date("Y"),
            // ],[
            //     'sallary' => DB::raw('sallary + ' . $requestSalary->usd),
            // ]);

            TransactionCustomNotification::action_request($agency_owner->id, 4,$requestSalary->usd,$requestSalary->host_id);
        }else{
            // update status
            $requestSalary->status = 4;
            $requestSalary->host_check =2;
            // add data to admin to check it
            AdminCheck::create([
                'request_id'    =>  $requestSalary->id,
                'admin_check'   =>  0,
                'type'          =>  "confirmation",
            ]);
            TransactionCustomNotification::action_request($agency_owner->id, 5,$requestSalary->usd,$requestSalary->host_id);
        }
        $requestSalary->save();
        return Common::apiResponse(1,__("salarytransaction::api_responses.edit_in_request"), []);
    }

    public function updatesalaryTransfer($agencyId, $usd)
    {
         AgencyTransferSalary::updateOrCreate([
            'agency_id' => $agencyId,
            "month" => date("m"),
            "year" => date("Y"),
        ],[
            'salary' => DB::raw('salary + ' . $usd),
        ]);
    }


}
