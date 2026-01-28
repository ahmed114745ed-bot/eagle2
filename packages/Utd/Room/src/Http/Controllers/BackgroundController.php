<?php

namespace Utd\Room\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Utd\Room\Services\BackgroundService;
use Utd\Room\Transformers\BackgroundResource;

class BackgroundController extends Controller
{
    public function __construct(
        protected BackgroundService $backgroundService
    ) {
    }

    /**
     * Get all enabled backgrounds
     */
    public function index(): JsonResponse
    {
        $backgrounds = $this->backgroundService->getEnabledBackgrounds();

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => BackgroundResource::collection($backgrounds),
        ]);
    }

    /**
     * Set room background
     */
    public function setBackground(Request $request, $roomId): JsonResponse
    {
        $request->validate([
            'background_id' => 'required|integer|exists:backgrounds,id',
        ]);

        try {
            $room = $this->backgroundService->setRoomBackground(
                $roomId,
                $request->background_id
            );

            return response()->json([
                'status' => true,
                'message' => 'Background updated',
                'data' => ['room_background' => $room->room_background],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
