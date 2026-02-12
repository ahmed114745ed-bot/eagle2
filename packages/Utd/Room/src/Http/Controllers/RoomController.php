<?php

namespace Utd\Room\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Room\Services\RoomService;
use Utd\Room\Transformers\RoomResource;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService
    ) {}

    /**
     * Get all rooms
     */
    public function index(Request $request): JsonResponse
    {
        $rooms = $this->roomService->getAllRooms($request);

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => RoomResource::collection($rooms),
        ]);
    }

    /**
     * Get room details
     */
    public function show($id): JsonResponse
    {
        try {
            $room = $this->roomService->getRoomDetails($id);

            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => new RoomResource($room),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get user's room
     */
    public function myRoom(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $room = $this->roomService->findRoomByUser($userId);

        if (! $room) {
            return response()->json([
                'status' => false,
                'message' => 'No room found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => new RoomResource($room),
        ]);
    }

    /**
     * Create room
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'room_name' => 'required|string|max:255',
            'type' => 'sometimes|in:audio,live,video',
        ]);

        try {
            $room = $this->roomService->createRoom(
                $request->all(),
                $request->user()
            );

            return response()->json([
                'status' => true,
                'message' => 'Room created successfully',
                'data' => new RoomResource($room),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update room
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $room = $this->roomService->updateRoom($id, $request->all());

            return response()->json([
                'status' => true,
                'message' => 'Room updated successfully',
                'data' => new RoomResource($room),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get room admins
     */
    public function admins($id): JsonResponse
    {
        try {
            $admins = $this->roomService->getRoomAdmins($id);

            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $admins,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Toggle writing disabled
     */
    public function toggleWriting(Request $request, $id): JsonResponse
    {
        try {
            $room = $this->roomService->toggleWriting($id);

            return response()->json([
                'status' => true,
                'message' => 'Writing status toggled',
                'data' => ['writing_disabled' => $room->writing_disabled],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
