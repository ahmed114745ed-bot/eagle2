<?php

namespace App\Tik\Services;

use App\Models\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Jobs\ResetCharisma;
use App\Models\EnteredRoom;
use App\Models\RoomVisitor;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use App\Jobs\SendNotificationToAllFollowers;
use App\Tik\Repositories\EnteranceRoomRepository;
use App\Http\Resources\Api\V1\EnterRoomCollection;
use Illuminate\Support\Facades\Schema;
use Modules\Charizma\Http\Services\UserCharismaService;
use Modules\CP\Entities\CpRoomHistory;

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
            // Log::info('charizma status' . $userId . '----' . $room->id);

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
                // Log::info('goooooooooooooood');
        //        $app_secert='a23b121a64ee9fab4567a2d75d00269d';
        //        if (!$this->checkSignature($app_secert,$request->signature, $request->timestamp, $request->nonce)) {
        //            Log::info('Invalid signature');
        //            return response()->json(['status' => 'success'], 200);
        //        }
        $event = $request->event;
        $roomId = $request->room_id;
        $userId = $request->user_account;



        $room = Room::select(['id', 'uid', 'count_room_socket', 'room_visitor', 'charizma_status', 'microphone'])->find($roomId);
        $user = User::find($userId);

        if (!$room || !$user) {
            // Log::info("Either room $roomId or user $userId not found.");
            return response()->json(['status' => 'Webhook received but room or user not found']);
        }

        $visitors = $this->updateRoomVisitorsBasedOnEvent($event, $room, $user->id);


        if ($event == 'room_login'){
            Log::info('room login : ' );
            $this->addUserToVisitors($room->id, $user->id);
            $user->now_room_uid = $room->uid;
        }elseif ($event == 'room_logout'  && $room->uid == $user->now_room_uid){
            $user->now_room_uid = 0;
        }
        if ($event == 'room_logout' ){
            Log::info('room logout : ' );
            $this->removeUserToVisitors($room->id, $user->id);
            $this->handleLeaveCp($user, $room);

        }

        if ($event == 'room_logout' && $room->charizma_status) {
            $this->handleCharismaStatusOnLogout($room, $user, $request->owner_id);
        }
        $user->save();

//        $count = RoomVisitor::query()->where('room_id', $room->id)->count();
//        DB::table('rooms')->where('id', $roomId)->update(['count_room_socket' => $count, 'room_visitor' => implode(",", $visitors)]);
//        $room->count_room_socket = $room->roomVisitors->count();
//        $room->room_visitor = implode(",", $visitors);
//        $room->save();

        // Log::info(json_encode(['userId'=>$userId, 'roomId' => $room->id, 'event' => $event]));
        return response()->json(['status' => 'Webhook processed successfully']);
    }
    public function updateRoomCountFromAgora(Request $request)
    {
    
        $data = $request->all(); 
    
        if (!isset($data[0]['eventType'], $data[0]['payload']['channelName'], $data[0]['payload']['lastUid'])) {
            return response()->json(['status' => 'Invalid Webhook Data'], 400);
        }
    
        $eventType = $data[0]['eventType'];
        $roomId = $data[0]['payload']['channelName'];
        $userId = $data[0]['payload']['lastUid'];
    
        Log::info('agora data', [
            $eventType,
            $userId,
            $roomId,
        ]);

        $room = Room::select(['id', 'uid', 'count_room_socket', 'room_visitor', 'charizma_status', 'microphone'])
                    ->find($roomId);
                  
        $user = User::find($userId);
    
        if (!$room || !$user) {
            return response()->json(['status' => 'Room or user not found'], 404);
        }
    
        $this->updateRoomVisitorsBasedOnEvent($eventType, $room, $user->id);
    
        if (in_array($eventType, [101, 103])) {
            $this->addUserToVisitors($room->id, $user->id);
            $user->now_room_uid = $room->uid;
        
        } elseif (in_array($eventType, [102, 104])) {
            $this->removeUserToVisitors($room->id, $user->id);
            $this->handleLeaveCp($user, $room);
           
            if ($room->uid == $user->id && Schema::hasColumn('rooms', 'is_live')) {
                $room->update(['is_live' => false]);
                Log::info('agora webhook triggered', [
                    $room->uid,
                    $user->id
                ]);
            }
            Log::info('not if', [
             
            ]);
            
        }
    
        if ($eventType == 'room_logout' && $room->charizma_status) {
            $ownerId = $data[0]['payload']['owner_id'] ?? null;
            $this->handleCharismaStatusOnLogout($room, $user, $ownerId);
        }
    
        $user->save();
    
        return response()->json(['status' => 'Webhook processed successfully']);
    
    }

    
    public function handleLeaveCp($user,$room)
    {
        $userId = $user->id;
        $this->removeUserCpInRoom($userId);
        return $this->sendCpLovelyMessage($room, $user);
    }
    public function removeUserCpInRoom(mixed $userId): void
    {
        CpRoomHistory::where("user_one_id", $userId)
            ->orWhere("user_two_id", $userId)->delete();
    }

    public function sendCpLovelyMessage($room, $user)
    {
        $cpRoomHistories = CpRoomHistory::where("room_id",$room->id)->get(['index1', 'index2']);
        $indices = $cpRoomHistories->map(function ($history) {
            return [$history->index1, $history->index2];
        })->toArray();

        $json = $this->cpMapJson($indices);

        Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);
    }

    public function cpMapJson($indices): string|false
    {
        $ms = [
            'messageContent' => [
                "message" => "cpLovelyZego",
                "data" => $indices,
            ]
        ];
        $json = json_encode($ms);
        return $json;
    }
    /*public function updateRoomCountFromZego(Request $request)
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
    }*/

    private function addUserToVisitors(int $roomId, int $userId)
    {
        RoomVisitor::query()->where(['user_id' => $userId])->delete();
        RoomVisitor::query()->create(['user_id' => $userId, 'room_id' => $roomId]);
    }

    private function removeUserToVisitors( int $roomId, int $userId)
    {
        RoomVisitor::query()->where(['user_id' => $userId, 'room_id'=> $roomId])->delete();
    }
  /*  private function addUserToVisitors(int $roomId, int $userId)
    {
        $enteranceRepo = $this->enteranceRoom(RoomVisitor::class);

        $enteranceRepo->addVisitor($roomId, $userId);
    }

    private function removeUserFromVisitors(int $roomId, int $userId)
    {
        $enteranceRepo = $this->enteranceRoom(RoomVisitor::class);
        $enteranceRepo->removeVisitor($roomId, $userId);
    }*/

    private function updateRoomVisitorsBasedOnEvent($event, $room, $userId)
    {
        $visitors = $room->room_visitor ? explode(',', $room->room_visitor) : [];

        if ($event == 'room_login' && !in_array($userId, $visitors)) {

            $visitors[] = $userId;
        } elseif ($event == 'room_logout') {
            UserHandling::calcTime($userId);
            $this->updateMicrophone($room->uid, $userId);
            $visitors = array_diff($visitors, [$userId]);
        }

        return array_values(array_unique($visitors));
    }

    private function handleCharismaStatusOnLogout($room, $user, $ownerId)
    {
        // Log::info('charizma status' . $user->id . '----' . $room->id);

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

    private function updateMicrophone($room_uid, $user_id)
    {
        $user = User::query()->find($user_id);
        if (!$user) return;
        $result  = Common::go_microphone_hand($room_uid, $user_id);

        $room = Room::query()->where('uid', $room_uid)->first();

        if (!$room) return;
        if ($result) {
            (new UserCharismaService())->RemoveUserRoomWhenLeaveMic($user_id, $room->id);
        }
    }

    ///////////////////////////////


    public function enterRoom($user, $request, $room_pass, $owner_id)
    {
        
        if ($request->type == 'random') {
            $owner_id = $this->roomRepository->randomOwner();
        }

        // if owner id not path throw error
        if (!$owner_id) return Common::apiResponse (0,'not found',null,404);
        //check if this user in black-list
        $black_list = Common::getUserBlackListInRoom($owner_id, $user->id);
        if ($black_list) return Common::apiResponse(false, __('You have been blocked by the other party'), null, 423);


        // get room by owner_id
        $room = $this->roomRepository->findRoomUser($owner_id, false);
        if (!$room)return Common::apiResponse (false,'No room yet, please create first',null,404);
        // if(($room->count_room_socket == 0 ) && $room->uid != $user_id && $room->pin != 1 )return Common::apiResponse(false, __('api_responses.closedRoom'), null, 402);

        $roomBlack = $room->room_black;
        if (!empty($roomBlack)) {
            $is_black = explode(',', $roomBlack);
            foreach ($is_black as $k => &$v) {
                $arr = explode("#", $v);
                $sjc = time() - $arr[1];
                $rt = $arr[2] - $sjc;
                $h = floor($rt / 3600);
                $r = $rt % 3600;
                $m = floor($r / 60);
                $s = $r % 60;
                if ($sjc < $arr[2] && $arr[0] == $user->id) {
                    $messageBlack = __('No entry for ') . $arr[2] / 60 . __(' minutes after being kicked out of the room');
                    $remainingTime = ['remaining_time' => "$h:$m:$s"];

                    return Common::apiResponse(false, __('No entry for ') . $arr[2] / 60 . __(' minutes after being kicked out of the room'));
                    // return [$messageBlack,$remainingTime];
                    // ['remaining_time' => "$h:$m:$s"]
                }

                if ($sjc >= $arr[2]) {
                    unset($is_black[$k]);
                }
            }
            $roomBlack = implode(",", $is_black);
            $this->roomRepository->updateRoomBlack($room, $roomBlack);
        }


        if ($room->room_pass &&  $owner_id != $user->id) {
            if (!$room_pass)  return Common::apiResponse(false,__('The room is locked, please enter the password'),null,409);
            if ($room->room_pass != $room_pass) return Common::apiResponse(false,__('Password is incorrect, please re-enter'),null,410);
        }


        /*if (!$request->is_update){
            if ($request->sendToZego != 'no') {
                dispatch(new EnterRoomZigoRequest($user, $room->id, $request->have_vip))->onQueue('enterRoomQueue');
            }
        }*/
        //        $this->getRoomTwoLastPk($room->id);

        if ($user->id == $owner_id) {
            $room->is_afk = 1;
            $room->save();
            if ($room->count_room_socket == 0) {
                dispatch(new SendNotificationToAllFollowers($room->uid))->onQueue('notification_heavy');
            }
        }
        $room_info = (new EnterRoomCollection($room,$user->id));

//        $keys = Common::getConfFromKey(['app_sign', 'zego_app_id']);
        $room_info = $room_info->toArray($request);
        // $room_info['zego_keys'] = $keys->mapWithKeys(function ($item){
        //     return [$item['name'] => (($item['name'] == 'zego_app_id') ? (integer)$item['value'] :$item['value'])];
        // });


        $this->updateRoom($user->id, $owner_id, $room);
        $this->enterTheRoomCreateOrUpdate($user->id, $owner_id, $room->id);
       // $this->updateRoomVisitor($user_id, $owner_id, $room);


        //$this->updateRoomVisitor($user_id, $owner_id, $room);

        //send to zego
        $user->enableSaving = false;
        $user->now_room_uid = (integer)$owner_id;
        $user->save();

        if (config('app.env') != "production") {
            RoomVisitor::firstOrCreate([
                'user_id'=>$user->id,
                'room_id'=>$room->id,
            ]);
        }

        return Common::apiResponse(true, '', $room_info);


    }
    private function updateRoom($user_id, $owner_id, Room &$room)
    {
       // $this->updateRoomVisitors($user_id, $owner_id, $room);

        if ($room->charizma_status && ($room->charizma_timestamp  + 86400) < now()->timestamp ){
            $room->charizma_timestamp = null;
            $room->charizma_status = false;
            dispatch(new ResetCharisma($room->id));
        }

        $room->save();
    }
    private function enterTheRoomCreateOrUpdate($user_id, $owner_id, $room_id)
    {
        EnteredRoom::query ()->updateOrCreate (
            [
                'uid'=>$user_id,
                'ruid'=>$owner_id,
                'rid'=>$room_id
            ],
            [
                'entered_at'=>now ()
            ]
        );
    }
}
