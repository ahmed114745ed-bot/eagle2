<?php

namespace App\Http\Controllers\utd;

use Exception;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\AgencyService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AgencyRequestsResource;


class AgencyController extends Controller
{
    public function __construct(private AgencyService $agencyService) {}

    public function index(Request $request)
    {
        try {
            $data = $this->agencyService->allRequests($request->id, $request->uuid, $request->per_page, $request->page, $request->status, $request->action);
            return Common::apiResponse(true, 'success', AgencyRequestsResource::collection($data));
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function actionRequestAgency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required|integer|exists:agencies,id',
            'status' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $this->agencyService->actionRequestAgency($request);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        if ($request->status != 1) {
            return Common::apiResponse(1, 'deleted request',  200);
        } else {
            return Common::apiResponse(1, 'accept request',  200);
        }
    }
}
