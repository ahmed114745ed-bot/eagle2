<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Repositories\Room\RoomRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Log;

class AgoraController extends Controller
{

    public function __construct(public RoomRepository $roomRepository, public UserRepository $userRepository)
    {

    }
    public function RtcToken(Request $request){

        $request->validate([
            'channel' => 'required',
            'expir' => 'nullable'
        ]);
        $user =$request->user();

        if($request->has('expir')){

            $token = generateRtcToken($request->channel,$user->id, $request->expir);

            return Common::apiResponse(true,'Success',$token);
        }

        $token = generateRtcToken($request->channel,$user->id);

        return Common::apiResponse(true,'Success',$token);
    }

    public function webhook(Request $request){
        Log::info('agora webhook triggered', [
            $request->all()
        ]);


        $event = $request->events[0];
        $name = $event['name'];
        $channelName = $event['channel'];
        $parts = explode('-', $channelName);
        if (count($parts) >= 3) {
            $roomId = $parts[1];
            $userId = $parts[2];
        } else {
            return;
        }

        $room = $this->roomRepository->findRoomUser($roomId);
        $user = $this->userRepository->findById($userId);

        if ($room === null || $user === null) {
            // Log::info("Room or User not found");
            return response()->json(['status' => 'Webhook received']);
        }

        // Log::info($name . '---' . $room->count_room_socket . '---' . $room->room_visitor . '---' . $user->now_room_uid);

        if ($name == 'channel_occupied' || $name == 'member_added') {
            $this->handleMemberAdded($room, $userId);
        } elseif ($name == 'channel_vacated' || $name == 'member_removed') {
            $this->handleMemberRemoved($room, $user, $request->owner_id);
        }

        $this->roomRepository->updateRoom($room);
        $this->userRepository->updateUser($user);

        return response()->json(['status' => 'Webhook received']);
    }
}
