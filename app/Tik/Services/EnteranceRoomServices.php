<?php

namespace App\Tik\Services;

use App\Facades\UserHandling;
use App\Helpers\Common;
use App\Models\Room;
use App\Models\RoomVisitor;
use App\Models\User;
use App\Tik\Repositories\EnteranceRoomRepository;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Charizma\Http\Services\UserCharismaService;

class EnteranceRoomServices
{
    protected $roomRepository;
    protected $userRepository;
    protected $enteranceRoomRepository;

    public function __construct(RoomRepository $roomRepository, UserRepository $userRepository)
    {
        $this->roomRepository = $roomRepository;
        $this->userRepository = $userRepository;
    }

    public function enteranceRoom($modelClass)
    {
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException('Invalid model class');
        }
    
        $modelInstance = new $modelClass;
        $enternaceRepo = new EnteranceRoomRepository($modelInstance);
        return $enternaceRepo;
    }
    

    ///////////////////////////////////////pusher////////////////////////////////////
    public function updateRoomCountFromPusher(Request $request)
    {
        if ($request->header('X-Pusher-Key') !== env('PUSHER_APP_KEY')) {
            abort(403, 'Invalid Pusher webhook request');
        }

        $event = $request->events[0];
        $name = $event['name'];
        $channelName = $event['channel'];
        $parts = explode('-', $channelName);
        if(count($parts) >= 3) {
            $roomId = $parts[1];
            $userId = $parts[2];
        } else {
            return;
        }

        $room = $this->roomRepository->findRoomUser($roomId);
        $user = $this->userRepository->findById($userId);

        if ($room === null || $user === null) {
            Log::info("Room or User not found");
            return response()->json(['status' => 'Webhook received']);
        }

        Log::info($name . '---' . $room->count_room_socket . '---' . $room->room_visitor . '---' . $user->now_room_uid);

        if ($name == 'channel_occupied' || $name == 'member_added') {
            $this->handleMemberAdded($room, $userId);
        } elseif ($name == 'channel_vacated' || $name == 'member_removed') {
            $this->handleMemberRemoved($room, $user, $request->owner_id);
        }

        $this->roomRepository->updateRoom($room);
        $this->userRepository->updateUser($user);

        return response()->json(['status' => 'Webhook received']);
    }

    private function handleMemberAdded($room, $userId)
    {
        $visitors = explode(',', $room->room_visitor);
        if ($visitors[0] == '') $visitors = [];
        if (!in_array($userId, $visitors)) {
            $visitors[] = $userId;
            $visitors = array_unique($visitors);
            $room->count_room_socket = count($visitors);
            $room->room_visitor = trim(implode(",", $visitors), ",");
        }
    }

    private function handleMemberRemoved($room, $user, $ownerId)
    {
        $userId = $user->id;
        if (@$room->charizma_status) {
            Log::info('charizma status' . $userId . '----' . $room->id);

            $userCharismaService = new UserCharismaService();
            $userCharismaService->resetUserCharisma($userId, $room->id);
            $userDataWithCharisma = $userCharismaService->getUserResetData($room->microphone, [$userId]);

            $ms = [
                'messageContent' => [
                    "message" => "updateCharisma",
                    'data' => $userDataWithCharisma
                ]
            ];
            $json = json_encode($ms);

            Common::sendToZego('SendCustomCommand', $room->id, $ownerId, $json);
        }

        $visitors = explode(',', $room->room_visitor);
        if (in_array($userId, $visitors)) {
            $index = array_search($userId, $visitors);
            unset($visitors[$index]);
            $room->count_room_socket = count($visitors);
            $room->room_visitor = trim(implode(",", $visitors), ",");
        }

        if ($room->uid == $userId->now_room_uid) {
            $user->now_room_uid = 0;
        }

        UserHandling::calcTime($userId);
    }

    //////////////////////////////////////////////////////room visitors//////////////////////////////////////////
    public function updateRoomCountFromZego(Request $request)
    {
        $event = $request->event;
        $roomId = $request->room_id;
        $userId = $request->user_account;

        $room = $this->roomRepository->findRoomUser($roomId);
        $user = $this->userRepository->findById($userId);

        if (!$room || !$user) {
            Log::info("Either room $roomId or user $userId not found.");
            return response()->json(['status' => 'Webhook received but room or user not found']);
        }

        $visitors = $this->updateRoomVisitorsBasedOnEvent($event, $room, $user->id);

        if ($event == 'room_login') {
            $this->addUserToVisitors($room->id, $user->id);
            $user->now_room_uid = $room->uid;
        } elseif ($event == 'room_logout' && $room->uid == $user->now_room_uid) {
            $user->now_room_uid = 0;
        }
        if ($event == 'room_logout') {
            $this->removeUserFromVisitors($room->id, $user->id);
        }

        if ($event == 'room_logout' && $room->charizma_status) {
            $this->handleCharismaStatusOnLogout($room, $user, $request->owner_id);
        }

        $this->userRepository->updateUser($user);

        $enteranceRepo = $this->enteranceRoom(RoomVisitor::class);
        $count = $enteranceRepo->countVisitors($room->id);
        $this->roomRepository->updateRoom($room, [
            'count_room_socket' => $count,
            'room_visitor' => implode(",", $visitors)
        ]);

        Log::info(json_encode(['userId' => $userId, 'roomId' => $room->id, 'event' => $event]));
        return response()->json(['status' => 'Webhook processed successfully']);
    }

    private function addUserToVisitors(int $roomId, int $userId)
    {
        $enteranceRepo = $this->enteranceRoom(RoomVisitor::class);

        $enteranceRepo->addVisitor($roomId, $userId);
    }

    private function removeUserFromVisitors(int $roomId, int $userId)
    {
        $enteranceRepo = $this->enteranceRoom(RoomVisitor::class);
        $enteranceRepo->removeVisitor($roomId, $userId);
    }

    private function updateRoomVisitorsBasedOnEvent($event, $room, $userId)
    {
        $visitors = $room->room_visitor ? explode(',', $room->room_visitor) : [];

        if ($event == 'room_login' && !in_array($userId, $visitors)) {

            $visitors[] = $userId;
        } elseif ($event == 'room_logout') {
            UserHandling::calcTime($userId);
            $this->updateMicrophone($room->uid,$userId);
            $visitors = array_diff($visitors, [$userId]);
        }

        return array_values(array_unique($visitors));
    }

    private function handleCharismaStatusOnLogout($room, $user, $ownerId)
    {
        Log::info('charizma status' . $user->id . '----' . $room->id);

        $userCharismaService = new UserCharismaService();
        $userCharismaService->resetUserCharisma($user->id, $room->id);
        $userDataWithCharisma = $userCharismaService->getUserResetData($room->microphone, [$user->id]);

        $ms = [
            'messageContent' => [
                "message" => "updateCharisma",
                'data' => $userDataWithCharisma
            ]
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $room->id, $ownerId, $json);
    }

    private function updateMicrophone($room_uid,$user_id)
    {
        $user = User::query()->find($user_id);
        if (!$user) return ;
        $result  =Common::go_microphone_hand($room_uid, $user_id);

        $room = Room::query ()->where ('uid',$room_uid)->first ();

        if (!$room) return ;
        if($result){
            (new UserCharismaService())-> RemoveUserRoomWhenLeaveMic($user_id, $room ->id);
        }
    }

    ///////////////////////////////



  
}
