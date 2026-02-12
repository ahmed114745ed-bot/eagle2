<?php

namespace Utd\Room\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Room\Services\MicrophoneService;
use Utd\Room\Transformers\RoomMicrophoneResource;

class MicrophoneController extends Controller
{
    public function __construct(
        protected MicrophoneService $microphoneService
    ) {}

    /**
     * Get microphones for room
     */
    public function index($roomId): JsonResponse
    {
        $microphones = $this->microphoneService->getRoomMicrophones($roomId);

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => RoomMicrophoneResource::collection($microphones),
        ]);
    }

    /**
     * Assign user to microphone
     */
    public function assign(Request $request, $roomId): JsonResponse
    {
        $request->validate([
            'position' => 'required|integer|min:0',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $microphone = $this->microphoneService->assignUserToMic(
                $roomId,
                $request->position,
                $request->user_id
            );

            return response()->json([
                'status' => true,
                'message' => 'User assigned to microphone',
                'data' => new RoomMicrophoneResource($microphone),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove user from microphone
     */
    public function remove(Request $request, $roomId): JsonResponse
    {
        $request->validate([
            'position' => 'required|integer|min:0',
        ]);

        try {
            $this->microphoneService->removeUserFromMic($roomId, $request->position);

            return response()->json([
                'status' => true,
                'message' => 'User removed from microphone',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update microphone status
     */
    public function updateStatus(Request $request, $roomId): JsonResponse
    {
        $request->validate([
            'position' => 'required|integer|min:0',
            'status' => 'required|integer',
        ]);

        try {
            $this->microphoneService->updateMicStatus(
                $roomId,
                $request->position,
                $request->status
            );

            return response()->json([
                'status' => true,
                'message' => 'Microphone status updated',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
