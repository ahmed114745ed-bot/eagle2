<?php

namespace App\Http\Controllers\Api\V2;


use App\Helpers\Common;
use App\Helpers\UserCommon;
use Illuminate\Http\Request;
use App\Tik\Services\AgencyService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AgencyJoinReqResource;
use App\Http\Resources\Api\V1\AllDataAgencyResource;
use App\Http\Resources\Api\V1\MyDataForAgancyResource;
use App\Http\Resources\Api\V1\MyDataForAgencyNewResource;
use App\Models\Agency;
use App\Models\AgencyJoinRequest;
use App\Models\AgencyUserJob;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Exception;

class AgencyController extends Controller
{
    protected $agencyService;

    public function __construct(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
    }

    public function joinRequest(Request $request)
    {
        $user   = $request->user();
        if (!$request->agency_id) return Common::apiResponse(0, __('api_responses.missing_params'), null, 422);

        try {
            $requests = $this->agencyService->joinAgency($user, $request);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }

        return Common::apiResponse(1, __('api_responses.request_sent'), AgencyJoinReqResource::collection($requests));
    }

    public function view(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        try {
            $agency = $this->agencyService->find($agencyId);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return Common::apiResponse(1, '', new AllDataAgencyResource($agency));
    }

    public function agencyMembers(Request $request)
    {
        $agencyId  = $request->user()->agency_id;
        try {
            $members = $this->agencyService->agencyMembers($agencyId);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }

        return Common::apiResponse(1, '', MyDataForAgancyResource::collection($members), 200, Common::getPaginates($members));
    }

    public function show_request(Request $request)
    {
        $userId = $request->user()->id;

        try {
            $requestList = $this->agencyService->showRequests($userId);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return Common::apiResponse(1, '', MyDataForAgancyResource::collection($requestList));
    }

    public function Accept_request(Request $request)
    {
        $accept    = $request->accept;
        $owner     = $request->user();
        if (!$request->user_id || !isset($request->accept)) {
            return Common::apiResponse(0, 'missing params');
        }

        try {
            $this->agencyService->requestAction($owner, $request);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        if ($accept == 0) {
            return Common::apiResponse(1, 'joinfalse');
        } elseif ($accept == 1) {
            return Common::apiResponse(1, 'joinSacsesAg');
        }
    }

    public function list_options_his(Request $request)
    {
        $agencyId      = $request->user()->agency_id;
        // Add the current month and year
        $monthsToInclude = $this->agencyService->listOption($agencyId);
        return Common::apiResponse(1, '', $monthsToInclude);
    }

    public function historyAgencySearch(Request $request)
    {
        $agencyId         = $request->user()->agency_id;
        $responseData = $this->agencyService->historySearch($agencyId, $request);
        return Common::apiResponse(1, '', $responseData);
    }

    public function update(Request $request, $id)
    {
        $userId = $request->user()->id;

        try {
            $agency = $this->agencyService->update($userId, $id, $request);
        } catch (\Exception $e) {

            return Common::apiResponse(0, $e->getMessage(), null, 500);
        }

        return Common::apiResponse(1, __('api_responses.agency_updated'), new AllDataAgencyResource($agency));
    }

    public function make_user_handling_requests(Request $request)
    {
        $user = $request->user();
        $agency = $user->ownAgency;
        if (!$user->ownAgency)   return Common::apiResponse(0, 'لا يوجد وكاله!', []);

//        try {
            $this->agencyService->userHandlingRequest($request->user_id, $agency->id);
      /*  } catch (ValidationException $exception){
            return Common::apiResponse(0, $exception->getMessage(), null, 422);
        } catch (\Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), null, 500);
        }*/

        return Common::apiResponse(1, 'تم اضافه المستخدم بنجاح', []);
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

            return Common::apiResponse(0, __('api_responses.notAdmin'));
        }
        $agency_id = $agency->id;
        $list_req = AgencyJoinRequest::where('agency_id', $agency_id);

        if ($type == "application") {
            $list_req1 = $list_req->where('status', 0)->with('user')->paginate(10);
            $list_req = MyDataForAgencyNewResource::collection($list_req1, 'application');
        } elseif ($type == "record") {
            $list_req = $list_req->where('status', '!=', 0)->with('user','admin')->paginate(10);
            $list_req = MyDataForAgencyNewResource::collection($list_req, 'record');
        }

        if ($list_req) {
            return Common::apiResponse(1, '', $list_req);
        }
        return Common::apiResponse(0, 'لا يوجد بيانات', []);
    }

    // public function showAgencyRequest(Request $request)
    // {
    //     $user = $request->user();
    //     $type = $request->type;

    //     // استدعاء الخدمة لمعالجة الطلب
    //     $data = $this->agencyService->showAgencyRequest($user, $type);
    //     return Common::apiResponse(1, '',$data);
    // }
}
