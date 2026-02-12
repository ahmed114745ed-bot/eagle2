<?php

namespace Utd\Agency\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Agency\Http\Resources\AgencyMemberResource;
use Utd\Agency\Http\Resources\AgencyResource;
use Utd\Agency\Services\AgencyService;

class AgencyController extends Controller
{
    public function __construct(
        protected AgencyService $agencyService
    ) {}

    /**
     * Get agency details
     */
    public function show($id)
    {
        try {
            $agency = $this->agencyService->find($id);

            return response()->json([
                'success' => true,
                'message' => 'Agency details',
                'data' => new AgencyResource($agency),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Join agency
     */
    public function joinRequest(Request $request)
    {
        try {
            $user = $request->user();
            $result = $this->agencyService->joinAgency($user, $request);

            return response()->json([
                'success' => true,
                'message' => 'Join request sent successfully',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get agency members
     */
    public function members($id)
    {
        try {
            $members = $this->agencyService->agencyMembers($id);

            return response()->json([
                'success' => true,
                'message' => 'Agency members',
                'data' => AgencyMemberResource::collection($members),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get agency target
     */
    public function target(Request $request, $id)
    {
        try {
            $user = $request->user();
            $result = $this->agencyService->agencyTarget($id, $user, $request);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get agency stars
     */
    public function stars(Request $request, $id)
    {
        try {
            $result = $this->agencyService->stars($id, $request);

            return response()->json([
                'success' => true,
                'message' => 'Agency stars',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get agency heroes
     */
    public function heroes(Request $request, $id)
    {
        try {
            $result = $this->agencyService->heroes($id, $request);

            return response()->json([
                'success' => true,
                'message' => 'Agency heroes',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Leave agency
     */
    public function leaveAgency(Request $request)
    {
        try {
            $user = $request->user();
            $result = $this->agencyService->leaveAgency($user, $user->agency_id);

            return response()->json([
                'success' => true,
                'message' => 'Leave request sent successfully',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user history
     */
    public function history(Request $request)
    {
        try {
            $uuid = $request->uuid ?? $request->user()->uuid;
            $result = $this->agencyService->historyLastThirtyDays($uuid);

            return response()->json([
                'success' => true,
                'message' => 'Agency history',
                'data' => $result,
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
            $result = $this->agencyService->getActiveAgencies(
                $request->id,
                $request->per_page ?? 20,
                $request->page ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'Active agencies',
                'data' => AgencyResource::collection($result),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
