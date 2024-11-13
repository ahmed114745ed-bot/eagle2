<?php

namespace App\Http\Controllers\Api\V1;


use Exception;
use App\Models\Room;
use App\Helpers\Common;
use Illuminate\Http\Request;
use GuzzleHttp\Promise\Utils;
use App\Tik\Services\PkService;
use App\Http\Services\RoomService;
use App\Http\Controllers\Controller;
use App\Traits\Rooms\ChangeRoomMode;
use Illuminate\Support\Facades\Auth;

class PkController extends Controller
{
    use ChangeRoomMode;

    protected $pkService;

    public function __construct(PkService $pkService)
    {
        $this->pkService = $pkService;
    }

    public function changeRoomImage(Request $request)
    {
        $ownerId = $request->owner_id;
        $room = Room::query()->where('uid', $ownerId)->first();

        if (!$room) Common::apiResponse(false, __('room not found'));
        $room->enableSaving = false;
        $room->is_pk_custom = true;
        $room->save();
        $json = $this->changeBackground($room, $request->owner_id, PK_IMAGE);


        Common::sendToZego('SendCustomCommand', $room->id, $request->user()->id, $json);

        return Common::apiResponse(true, __('success process'));
    }

    public function createPK(Request $request)
    {
        $userId = Auth::id();
        try {
            [$pk, $roomId] = $this->pkService->create($request, $userId);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        $mc   = [
            'messageContent' => [
                'message' => 'startPK',
                'PkTime'  => $request->minutes
            ]
        ];
        $json = json_encode($mc);
        $response = Common::sendToZego('SendCustomCommand', $roomId, $userId, $json);

        return Common::apiResponse(1, __('api_responses.created'), ['pk_id' => $pk->id], 201);
    }

    public function closePK(Request $request)
    {

        if (!@$request->owner_id || !@$request->pk_id) Common::apiResponse(0, __('api_responses.missing_params'), null, 422);
        try {
            $pk = $this->pkService->closePk($request->pk_id);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        $mc = [
            'messageContent' => [
                'message' => 'closePk',
                'scoreTeam1' => $pk->t1_score,
                'scoreTeam2' => $pk->t2_score,
                'percentagepk_team1' => $pk->t1_per,
                'percentagepk_team2' => $pk->t2_per,
                'winner_Team' => $pk->winner,
            ]
        ];
        $json = json_encode($mc);
        Common::sendToZego('SendCustomCommand', $pk->room_id, $request->user()->id, $json);
        return Common::apiResponse(1, __('api_responses.closed'), null, 201);
    }

    public function hidePk(Request $request)
    {
        if (!$request->owner_id) return Common::apiResponse(0, __('api_responses.missing_params'), null, 422);

        try {
            $room = $this->pkService->showPkOrHide($request->owner_id,0);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        $d = [
            "messageContent" => [
                "message" => "hidePK",
            ]
        ];
        $json = json_encode($d);
        $jsons[] = $json;
        $jsons[] = $this->changeBackground($room, $request->owner_id, (new RoomService())->getRoomBackground($room));
        $promises = Common::sendToZego3('SendCustomCommand', $room->id, $request->user()->id, $jsons);

        try {
            Utils::unwrap($promises);
        } catch (\Throwable $e) {
        }
        //        Common::sendToZego ('SendCustomCommand',$room->id,$user->id,$json);
        return Common::apiResponse(1, 'done', null, 201);
    }
    public function showPK(Request $request)
    {
        if (!$request->owner_id) return Common::apiResponse(0, __('api_responses.missing_params'), null, 422);
        try {
            $room = $this->pkService->showPkOrHide($request->owner_id,1);

        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        \Log::info($room->id . ' rooms');
       $jsons[] =  $this->changeBackground($room, $request->owner_id, PK_IMAGE);
        $mc   = [
            'messageContent' => [
                'message' => 'showPK'
            ]
        ];
        $jsons[] = json_encode($mc);
        // Common::sendToZego('SendCustomCommand', $room->id, $request->user()->id, $jsons);
        $promises = Common::sendToZego3('SendCustomCommand', $room->id, $request->user()->id, $jsons);

        try {
            Utils::unwrap($promises);
        } catch (\Throwable $e) {
        }
        return Common::apiResponse(1, 'done', null, 201);
    }

    public function changeBackground(Room $room, int $owner_id, string $image = '')
    {
        $data = [
            "messageContent" => [
                "message"       => "changeBackground",
                "imgbackground" => $image ?: "",
                "roomIntro"     => $room->room_intro ?: "",
                "roomImg"       => $room->room_cover ?: "",
                "room_type"     => @$room->myType->name ?: "",
                "room_name"     => @$room->room_name ?: ""
            ]
        ];
        $json = json_encode($data);
        // Common::sendToZego3('SendCustomCommand', $room->id, $owner_id, $json);
        return $json;
    }
}
