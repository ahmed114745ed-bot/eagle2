<?php

namespace App\Http\Controllers\utd;

use Exception;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\AgencyService;
use App\Tik\Services\ReportService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ActiveAgencyResource;
use App\Http\Resources\AgencyRequestsResource;


class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function reports(Request $request)
    {
        $data = $this->reportService->report($request);
        return Common::apiResponse(true, 'done', $data);
    }
}
