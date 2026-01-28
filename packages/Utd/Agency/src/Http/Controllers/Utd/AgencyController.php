<?php

namespace Utd\Agency\Http\Controllers\Utd;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Agency\Services\AgencyService;
use Utd\Agency\Http\Resources\AgencyResource;
use Utd\Agency\Http\Resources\AgencyRequestResource;

class AgencyController extends Controller
{
    public function __construct(
        protected AgencyService $agencyService
    ) {}

    /**
     * Get all agency requests
     */
    public function index(Request $request)
    {
        try {
            $data = $this->agencyService->allRequest();
            
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => AgencyRequestResource::collection($data),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Action on agency request
     */
    public function actionRequestAgency(Request $request)
    {
        try {
            $action = $request->action;
            $agencyId = $request->agency_id;
            $userId = $request->user_id;

            if ($action === 'accept') {
                $this->agencyService->acceptRequest($agencyId, $userId);
                $message = 'Request accepted';
            } else {
                $this->agencyService->rejectRequest($agencyId, $userId);
                $message = 'Request rejected';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get active agencies
     */
    public function activeAgencies(Request $request)
    {
        try {
            $data = $this->agencyService->getActiveAgencies(
                $request->id,
                $request->per_page ?? 20,
                $request->page ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => AgencyResource::collection($data),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all agencies
     */
    public function allAgencies(Request $request)
    {
        try {
            $data = $this->agencyService->getActiveAgencies(
                $request->id,
                $request->per_page ?? 50,
                $request->page ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => AgencyResource::collection($data),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get active agencies members
     */
    public function activeAgenciesMembers(Request $request)
    {
        try {
            $agencyId = $request->agency_id;
            $data = $this->agencyService->agencyMembers($agencyId);

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create agency
     */
    public function create(Request $request)
    {
        try {
            $data = $this->agencyService->createAgency($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Agency created successfully',
                'data' => new AgencyResource($data),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update agency
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $this->agencyService->updateAgency($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Agency updated successfully',
                'data' => new AgencyResource($data),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete agency
     */
    public function destroy($id)
    {
        try {
            $this->agencyService->deleteAgency($id);

            return response()->json([
                'success' => true,
                'message' => 'Agency deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get agency report
     */
    public function report(Request $request)
    {
        try {
            $data = $this->agencyService->getAgencyReport(
                $request->id,
                $request->month,
                $request->year,
                $request->per_page ?? 20,
                $request->page ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
