<?php

namespace Utd\Agency\Http\Controllers\Api\V2\Dashboard;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Utd\Agency\Contracts\AgencyServiceInterface;
use Utd\Agency\Exports\HostDailyDataExport;
use Utd\Agency\Facades\AgencyHelper;
use Utd\Agency\Traits\ResolvesExternalDependencies;
use Utd\Agency\Transformers\HostDailyReportResource;

class AgencyAppController extends Controller
{
    use ResolvesExternalDependencies;

    protected $agencyService;

    public function __construct(?AgencyServiceInterface $agencyService = null)
    {
        // Use injected service if available, otherwise resolve from config
        $this->agencyService = $agencyService ?? $this->getAgencyService();

        // If still null, throw exception
        if (! $this->agencyService) {
            throw new RuntimeException('AgencyService is not configured. Please configure it in agency-dependencies.php');
        }
    }

    public function agency_data(Request $request)
    {

        try {
            $data = $this->agencyService->dataAgency();
        } catch (Exception $e) {
            return AgencyHelper::apiResponse(false, $e->getMessage(), null, 407);
        }

        return AgencyHelper::apiResponse(1, '', $data, 200);
    }

    public function host_report($id)
    {

        try {
            $data = $this->agencyService->hostReport($id);
        } catch (Exception $e) {
            return AgencyHelper::apiResponse(false, $e->getMessage(), null, 407);
        }

        return AgencyHelper::apiResponse(1, '', $data, 200);
    }

    public function host_daily_report(Request $request)
    {
        $date = $request->date;

        try {
            $hosts = $this->agencyService->hostDailyReport($request);
        } catch (Exception $e) {
            return AgencyHelper::apiResponse(false, $e->getMessage(), null, 407);
        }

        return AgencyHelper::apiResponse(1, '', HostDailyReportResource::collection($hosts), 200);
    }

    public function host_daily_export_data(Request $request)
    {
        $date = $request->date;

        try {
            $hosts = $this->agencyService->hostDailyReport($request);
        } catch (Exception $e) {
            return AgencyHelper::apiResponse(false, $e->getMessage(), null, 407);
        }
        $data = HostDailyReportResource::collection($hosts);

        return Excel::download(new HostDailyDataExport($data), 'data.xlsx');
    }

    public function host_agency_edit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'notice' => 'nullable',
        ]);
        try {
            $agency = $this->agencyService->editAgency($request);
        } catch (Exception $e) {
            return AgencyHelper::apiResponse(false, $e->getMessage(), null, 407);
        }

        return AgencyHelper::apiResponse(1, '', $agency, 200);
    }
}
