<?php

namespace Utd\Charizma\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Utd\Charizma\Entities\ExtraDataInRoom;
use Utd\Charizma\Services\UserCharismaService;
use Utd\Room\Entities\Room;

class CharizmaController extends Controller
{
    public function __construct(private UserCharismaService $userCharismaService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): Renderable
    {
        return view('charizma::index');
    }

    /**
     * @return mixed
     */
    public function roomCharisma(int $room_id)
    {
        return $this->userCharismaService->roomCharisma($room_id);
    }

    public function changeStatus(Request $request): JsonResponse
    {
        $request->validate([
            'room_id' => 'required|integer',
        ]);

        $roomId = $request->room_id;
        $room = Room::where('id', $roomId)->where('uid', auth()->id())->first();

        if (! $room) {
            return Common::apiResponse(0, __('api_responses.room_not_found'), null, 4043);
        }

        $room->charizma_status = ! $room->charizma_status;
        $isFalse = $room->charizma_status === 0;
        $room->charizma_timestamp = $isFalse ? null : now()->timestamp;
        $room->save();

        $ms = [
            'messageContent' => [
                'message' => $room->charizma_status ? 'startCharisma' : 'closeCharisma',
            ],
        ];
        $json = json_encode($ms);
        Common::sendToZego('SendCustomCommand', $roomId, Auth::id(), $json);

        // Delete all charisma in room
        if ($isFalse) {
            $this->userCharismaService->removeRoomCharisma($roomId);
        }

        return Common::apiResponse(
            1,
            'charisma status is changed',
            [
                'room_id' => $room->id,
                'charisma_status' => $room->charizma_status,
            ],
            200
        );
    }

    public function reset(Request $request)
    {
        $request->validate([
            'room_id' => 'nullable|integer',
            'owner_id' => 'required_without:room_id',
        ]);

        $roomId = $request->room_id;
        $room = $roomId
            ? Room::find($roomId)
            : Room::where('uid', $request->owner_id)->where('type', 'audio')->first();

        if (! $room) {
            return Common::apiResponse(false, 'No Room Founded');
        }

        ExtraDataInRoom::query()->where('room_id', $room->id)->update(['total' => 0]);

        $collections = [
            'charisma' => $this->roomCharisma($room->id),
        ];

        $ms = [
            'messageContent' => [
                'message' => 'updateCharisma',
                'data' => $this->roomCharisma($room->id),
            ],
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $room->id, $room->uid, $json);

        return Common::apiResponse(true, 'successfully', $collections);
    }
}
