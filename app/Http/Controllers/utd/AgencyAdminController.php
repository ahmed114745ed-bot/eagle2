<?php

namespace App\Http\Controllers\utd;

use Exception;
use App\Models\Config;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\ReelsService;
use App\Tik\Services\AgencyService;
use App\Http\Controllers\Controller;
use App\Tik\Services\AgencyAdminService;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AgencyRequestsResource;


class AgencyAdminController extends Controller
{

    public function __construct(private AgencyAdminService $agencyAdminService) {}

    public function index(Request $request)
    {
        try {
            $data = $this->agencyAdminService->allAgencyAdmin($request->id, $request->per_page, $request->page);
            return Common::apiResponse(true, 'success', AgencyRequestsResource::collection($data));
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
