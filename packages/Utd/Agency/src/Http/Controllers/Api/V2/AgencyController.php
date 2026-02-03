<?php

namespace Utd\Agency\Http\Controllers\Api\V2;

use Cache;
use App\Models\User;
use App\Models\Agency;
use App\Models\Setting;
use App\Helpers\UserCommon;
use Utd\Agency\Facades\AgencyHelper;
use Illuminate\Http\Request;
use App\Models\AgencyUserJob;
use PHPUnit\Framework\Exception;
use App\Models\AgencyJoinRequest;
use Utd\Agency\Services\AgencyService;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Utd\Agency\Http\Resources\AdminsAgencyResource;
use Utd\Agency\Http\Resources\AgencyDetailsResource;
use Utd\Agency\Http\Resources\AgencyJoinReqResource;
use Utd\Agency\Http\Resources\AllDataAgencyResource;
use Utd\Agency\Http\Resources\HistoryAgencyResource;
use Utd\Agency\Http\Resources\SenderGiftLogResource;
use Utd\Agency\Http\Resources\MyDataForAgencyResource;
use Utd\Agency\Http\Resources\ReceiverGiftLogResource;
use Utd\Agency\Http\Resources\MyDataForAgencyNewResource;
use Utd\Agency\Http\Resources\JoinedAgencyResource;

class AgencyController extends Controller
{
    protected $agencyService;

    public function __construct(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
    }

    public function joinRequest(Request $request)
    {
        $app_feature = Cache::get('host_agency');
        if (!$app_feature) {
            throw new Exception(__('Agency Feature is Disabled, Contact the administration'));
        }

        $user   = $request->user();
        if ($user->is_bd) return AgencyHelper::apiResponse(false, 'You are BD, You can\'t join agency', null, 407);

        if (!$request->agency_id) return AgencyHelper::apiResponse(0, __('api_responses.missing_params'), null, 422);

        try {
            $requests = $this->agencyService->joinAgency($user, $request);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }

        return AgencyHelper::apiResponse(1, __('api_responses.request_sent'), AgencyJoinReqResource::collection($requests));
    }

    public function view(Request $request)
    {
        $app_feature = Cache::get('host_agency');
        if (!$app_feature) {
            throw new Exception(__('Agency Feature is Disabled, Contact the administration'));
        }
        $agencyId = request()->get('id', $request->user()->agency_id);

        try {
            $agency = $this->agencyService->find($agencyId);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '', new AllDataAgencyResource($agency));
    }

    public function agencyDetails($id)
    {
        $app_feature = Cache::get('host_agency');
        if (!$app_feature) {
            throw new Exception(__('Agency Feature is Disabled, Contact the administration'));
        }

        try {
            $agency = $this->agencyService->find($id);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '', new AgencyDetailsResource($agency));
    }

    public function history($id, Request $request)
    {
        $user = $request->user();
        $app_feature = Cache::get('host_agency');
        if (!$app_feature) {
            throw new Exception(__('Agency Feature is Disabled, Contact the administration'));
        }

        try {
            $agency = $this->agencyService->find($id);
            request()->is_onwer_agency = ($user->id !=  $agency->app_owner_id);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '', new HistoryAgencyResource($agency));
    }

    public function admin($id)
    {
        try {
            $agency = $this->agencyService->find($id);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '', AdminsAgencyResource::collection($agency->admins));
    }

    public function agencyTargetDetails($agencyId, Request $request)
    {
        $user = $request->user();
        try {
            $response = $this->agencyService->agencyTarget($user->agency_id, $user, $request);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(
            1,
            $response['message'],
            $response['data'],
            $response['status'],
            '',
            'users_target'
        );
    }

    public function star($id, Request $request)
    {
        try {
            $data = $this->agencyService->stars($id, $request);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '',  ReceiverGiftLogResource::collection($data), 200);
    }

    public function heroes($id, Request $request)
    {
        try {
            $data = $this->agencyService->heroes($id, $request);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '',  SenderGiftLogResource::collection($data), 200);
    }

    public function agencyMembers(Request $request)
    {
        $agencyId  = $request->user()->agency_id;
        try {
            $members = $this->agencyService->agencyMembers($agencyId);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }

        return AgencyHelper::apiResponse(1, '', MyDataForAgencyNewResource::collection($members), 200, AgencyHelper::getPaginates($members));
    }

    public function show_request(Request $request)
    {
        $userId = $request->user()->id;

        try {
            $requestList = $this->agencyService->showRequests($userId);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '', MyDataForAgencyNewResource::collection($requestList));
    }

    public function Accept_request(Request $request)
    {
        $accept    = $request->accept;
        $owner     = $request->user();
        if (!$request->user_id || !isset($request->accept)) {
            return AgencyHelper::apiResponse(0, 'missing params');
        }
        try {
            $this->agencyService->requestAction($owner, $request);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        if ($accept === 0 || $accept === false) {

            return AgencyHelper::apiResponse(1, 'joinfalse');
        } elseif ($accept === 1 || $accept === true) {
            return AgencyHelper::apiResponse(1, 'joinSacsesAg');
        }
    }

    public function list_options_his(Request $request)
    {
        $agencyId      = $request->user()->agency_id;
        $monthsToInclude = $this->agencyService->listOption($agencyId);
        return AgencyHelper::apiResponse(1, '', $monthsToInclude);
    }

    public function historyAgencySearch(Request $request)
    {
        $agencyId         = $request->user()->agency_id;
        $responseData = $this->agencyService->historySearch($agencyId, $request);
        return AgencyHelper::apiResponse(1, '', $responseData);
    }

    public function update(Request $request, $id)
    {
        $app_feature = Cache::get('host_agency');
        if (!$app_feature) {
            throw new Exception(__('Agency Feature is Disabled, Contact the administration'));
        }

        $userId = $request->user()->id;

        try {
            $agency = $this->agencyService->update($userId, $id, $request);
        } catch (\Exception $e) {

            return AgencyHelper::apiResponse(0, $e->getMessage(), null, 500);
        }

        return AgencyHelper::apiResponse(1, __('api_responses.agency_updated'), new AllDataAgencyResource($agency));
    }

    public function make_user_handling_requests(Request $request)
    {
        $user = $request->user();
        $type = $request->type ?? null;
        $agency = $user->ownAgency;
        if (!$user->ownAgency)   return AgencyHelper::apiResponse(0, 'لا يوجد وكاله!', []);

        $mass =  $this->agencyService->userHandlingRequest($request->user_id, $agency->id, $type);

        return AgencyHelper::apiResponse(1, $mass, []);
    }

    public function showAgencyRequest(Request $request)
    {
        $user   = $request->user();
        $type = $request->type;

        $admin = AgencyUserJob::where('user_id', $user->id)->where('type', 'requestManger')->first();
        if ($admin) {
            $agency = Agency::where('id', $admin->agency_id)->first();
        } else {
            $agency = Agency::where('app_owner_id', $user->id)->first();
        }

        if (!$agency) {
            return AgencyHelper::apiResponse(0, __('api_responses.notAdmin'));
        }
        
        $agency_id = $agency->id;

        try {
            if ($type == "application") {
                // Get pending requests (status = 0)
                $list_req = $this->agencyService->getAgencyJoinRequests($agency_id, 0, true, 10);
                $formatted = MyDataForAgencyNewResource::collection($list_req, 'application');
            } elseif ($type == "record") {
                // Get accepted and rejected requests (status != 0)
                $allRequests = $this->agencyService->getAgencyJoinRequests($agency_id, null, false);
                $filteredRequests = $allRequests->filter(function($item) {
                    return $item->status != 0;
                });
                $formatted = MyDataForAgencyNewResource::collection($filteredRequests, 'record');
            } else {
                return AgencyHelper::apiResponse(0, 'Invalid type parameter', []);
            }
            
            return AgencyHelper::apiResponse(1, '', $formatted, 200);
        } catch (\Exception $exception) {
            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function agenciesCharge(Request $request)
    {
        try {
            $agencies = $this->agencyService->allAgencyCharged($request->agency_id);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '',  AllDataAgencyResource::collection($agencies));
    }

    public function gitOldAgencies(Request $request)
    {
        $userId   = $request->user()->id;
        try {
            $agency = $this->agencyService->gitOldAgencies($userId);
        } catch (\Exception $exception) {

            return AgencyHelper::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return AgencyHelper::apiResponse(1, '', JoinedAgencyResource::collection($agency));
    }
}
