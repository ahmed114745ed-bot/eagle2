<?php

namespace App\Tik\Services;

use App\Facades\UserHandling;
use App\Helpers\Common;
use App\Http\Resources\Api\V1\EnterRoomCollection;
use App\Http\Resources\Api\V1\EnterRoomLiveCollection;
use App\Jobs\ResetCharisma;
use App\Jobs\SendNotificationToAllFollowers;
use App\Models\EnteredRoom;
use App\Models\Room;
use App\Models\RoomMicrophone;
use App\Models\RoomVisitor;
use App\Models\User;
use App\Tik\Repositories\EnteranceRoomRepository;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Charizma\Http\Services\UserCharismaService;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Events\Chat;
use Modules\Chat\Events\Conversation;
use Modules\Chat\Events\OpenChat;
use Modules\Chat\Http\Resources\ChatMessageResource;
use Modules\Chat\Http\Resources\ChatRoomResourcePusher;
use Modules\CP\Entities\CpRoomHistory;
use Modules\RoomCup\Helpers\RoomCupHelper;
use Modules\TaskStream\Services\TaskStreamService;

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
            return response()->json(['status' => 'Webhook received']);
        }

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
        // Use repository for visitor operations (includes dual-write to legacy column)
        $visitorRepo = app(\App\Repositories\RoomVisitorRepository::class);
        if (!$visitorRepo->isVisitor($room->id, $userId)) {
            $visitorRepo->addVisitor($room->id, $userId);

            // Update count (repository already updates room_visitor column via dual-write)
            $room->count_room_socket = $visitorRepo->getVisitorCount($room->id);
        }
    }

    private function handleMemberRemoved($room, $user, $ownerId)
    {
        $userId = $user->id;
        if (@$room->charizma_status) {

            $userCharismaService = new UserCharismaService();
            $userCharismaService->resetUserCharisma($userId, $room->id);
            $userDataWithCharisma = $userCharismaService->getUserResetData2($room, [$userId]);

            $ms = [
                'messageContent' => [
                    "message" => "updateCharisma",
                    'data' => $userDataWithCharisma
                ]
            ];
            $json = json_encode($ms);

            Common::sendToZego('SendCustomCommand', $room->id, $ownerId, $json);
        }

        // Use repository for visitor operations (includes dual-write to legacy column)
        $visitorRepo = app(\App\Repositories\RoomVisitorRepository::class);
        if ($visitorRepo->isVisitor($room->id, $userId)) {
            $visitorRepo->removeVisitor($room->id, $userId);

            // Update count (repository already updates room_visitor column via dual-write)
            $room->count_room_socket = $visitorRepo->getVisitorCount($room->id);
        }

        if ($room->uid == $user->now_room_uid) {
            $user->now_room_uid = 0;
        }

        UserHandling::calcTime($userId);
    }

    //////////////////////////////////////////////////////room visitors//////////////////////////////////////////
    public function updateRoomCountFromZego(Request $request)
    {
        //        $app_secert='a23b121a64ee9fab4567a2d75d00269d';
        //        if (!$this->checkSignature($app_secert,$request->signature, $request->timestamp, $request->nonce)) {
        //            return response()->json(['status' => 'success'], 200);
        //        }
        $event = $request->event;
        $roomId = $request->room_id;
        $userId = $request->user_account;

        /** @var Room $room */
        $room = Room::select(['id', 'uid', 'count_room_socket', 'charizma_status', 'microphone'])->find($roomId);
        $user = User::find($userId);

        if (!$room || !$user) {
            return response()->json(['status' => 'Webhook received but room or user not found']);
        }

        $visitors = $this->updateRoomVisitorsBasedOnEvent($event, $room, $user->id);

        if ($event == 'room_login') {
            $this->addUserToVisitors($room->id, $user->id);
            $user->now_room_uid = $room->id;
        } elseif ($event == 'room_logout'  && $room->uid == $user->now_room_uid) {
            $user->now_room_uid = 0;
        }
        if ($event == 'room_logout') {

            if ($user->id === $room->uid) {
                $room->is_afk = 0;
            }
            $this->removeUserToVisitors($room->id, $user->id);
            $this->handleLeaveCp($user, $room);
        }

        if ($event == 'room_logout' && $room->charizma_status) {
            $this->handleCharismaStatusOnLogout($room, $user, $request->owner_id);
        }
        $user->save();
        $room->save();



        //        $count = RoomVisitor::query()->where('room_id', $room->id)->count();
        //        DB::table('rooms')->where('id', $roomId)->update(['count_room_socket' => $count, 'room_visitor' => implode(",", $visitors)]);
        //        $room->count_room_socket = $room->roomVisitors->count();
        //        $room->room_visitor = implode(",", $visitors);
        //        $room->save();

        return response()->json(['status' => 'Webhook processed successfully']);
    }

    public function updateRoomCountFromZego2(Request $request)
    {
        $event = $request->event;
        $roomId = $request->room_id;
        $userId = $request->user_account;

        /** @var Room $room */
        $room = Room::select(['id', 'uid', 'count_room_socket', 'charizma_status', 'microphone'])->find($roomId);
        $user = User::find($userId);

        if (!$room || !$user) {
            return response()->json(['status' => 'Webhook received but room or user not found']);
        }

        $taskStreamRoom = $room->taskStreamRoom()->first();
        if ($taskStreamRoom) {
            app(TaskStreamService::class)->leave(['task_stream_id' => $taskStreamRoom->task_stream_id]);
        }

        $visitors = $this->updateRoomVisitorsBasedOnEvent2($event, $room, $user->id);

        if ($event == 'room_login') {
            $this->addUserToVisitors($room->id, $user->id);
            $user->now_room_uid = $room->id;
        } elseif ($event == 'room_logout'  && $room->uid == $user->now_room_uid) {
            $user->now_room_uid = 0;
        }
        if ($event == 'room_logout') {

            if ($user->id === $room->uid) {
                $room->is_afk = 0;
            }
            $this->removeUserToVisitors($room->id, $user->id);
            $this->handleLeaveCp($user, $room);
        }

        if ($event == 'room_logout' && $room->charizma_status) {
            $this->handleCharismaStatusOnLogout2($room, $user, $request->owner_id);
        }
        $user->save();
        $room->save();

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

        $room = Room::select(['id', 'uid', 'count_room_socket', 'room_visitor', 'charizma_status', 'microphone', 'type'])
            ->find($roomId);

        $user = User::find($userId);

        if (!$room || !$user) {
            return response()->json(['status' => 'Room or user not found'], 404);
        }

        $this->updateRoomVisitorsBasedOnEvent($eventType, $room, $user->id);

        if (in_array($eventType, [101, 103])) {

            $this->addUserToVisitors($room->id, $user->id);
            $user->now_room_uid = $room->id;

            if ($room->uid == $user->id && Schema::hasColumn('rooms', 'is_live')) {
                $room->update(['is_live' => true]);
            }
        } elseif (in_array($eventType, [102, 104])) {
            $this->removeUserToVisitors($room->id, $user->id);
            $this->handleLeaveCp($user, $room);

            if (
                Schema::hasColumn('rooms', 'is_live') &&
                $room->uid == $user->id &&
                $room->type !== 'audio'
            ) {
                $room->update(['is_live' => false]);
            }
        }

        if ($eventType == 'room_logout' && $room->charizma_status) {
            $ownerId = $data[0]['payload']['owner_id'] ?? null;
            $this->handleCharismaStatusOnLogout($room, $user, $ownerId);
        }

        $user->save();

        return response()->json(['status' => 'Webhook processed successfully']);
    }


    public function handleLeaveCp($user, $room)
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
        $cpRoomHistories = CpRoomHistory::where("room_id", $room->id)->get(['index1', 'index2']);
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

        return response()->json(['status' => 'Webhook processed successfully']);
    }*/

    private function addUserToVisitors(int $roomId, int $userId)
    {
        RoomVisitor::query()->where(['user_id' => $userId])->delete();
        RoomVisitor::query()->create(['user_id' => $userId, 'room_id' => $roomId]);
    }

    private function removeUserToVisitors(int $roomId, int $userId)
    {
        RoomVisitor::query()->where(['user_id' => $userId, 'room_id' => $roomId])->delete();
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
        $visitorRepo = app(\App\Repositories\RoomVisitorRepository::class);

        if ($event == 'room_login') {
            $visitorRepo->addVisitor($room->id, $userId);
        } elseif ($event == 'room_logout') {
            UserHandling::calcTime($userId);
            $this->updateMicrophone($room->uid, $userId);
            $visitorRepo->removeVisitor($room->id, $userId);
        }

        // Return visitor IDs for backward compatibility
        return $visitorRepo->getVisitorIds($room->id)->toArray();
    }

    private function updateRoomVisitorsBasedOnEvent2($event, $room, $userId)
    {
        $visitorRepo = app(\App\Repositories\RoomVisitorRepository::class);

        if ($event == 'room_login') {
            $visitorRepo->addVisitor($room->id, $userId);
        } elseif ($event == 'room_logout') {
            UserHandling::calcTime($userId);
            $this->updateMicrophone2($room->uid, $userId);
            $visitorRepo->removeVisitor($room->id, $userId);
        }

        // Return visitor IDs for backward compatibility
        return $visitorRepo->getVisitorIds($room->id)->toArray();
    }

    private function handleCharismaStatusOnLogout($room, $user, $ownerId)
    {
        $userCharismaService = new UserCharismaService();
        $userCharismaService->resetUserCharisma($user->id, $room->id);
        $userDataWithCharisma = $userCharismaService->getUserResetData2($room, [$user->id]);

        $ms = [
            'messageContent' => [
                "message" => "updateCharisma",
                'data' => $userDataWithCharisma
            ]
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $room->id, $ownerId, $json);
    }

    private function handleCharismaStatusOnLogout2($room, $user, $ownerId)
    {
        $userCharismaService = new UserCharismaService();
        $userCharismaService->resetUserCharisma($user->id, $room->id);
        $userDataWithCharisma = $userCharismaService->getUserResetData2($room, [$user->id]);

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

    private function updateMicrophone2($room_uid, $user_id)
    {
        $user = User::query()->find($user_id);
        if (!$user) return;
        $result  = Common::go_microphone_hand_2($room_uid, $user_id);

        $room = Room::query()->where('uid', $room_uid)->first();

        if (!$room) return;
        if ($result) {

            (new UserCharismaService())->RemoveUserRoomWhenLeaveMic($user_id, $room->id);
        }
    }
    ///////////////////////////////


    public function enterRoom($user, $request, $room_pass, Room $room)
    {
        $owner_id = $room->uid;
        if ($request->sub_type == 'random') {
            $owner_id = $this->roomRepository->randomOwner();
        }

        // if owner id not path throw error
        if (!$owner_id) return Common::apiResponse(0, 'not found', null, 404);
        //check if this user in black-list
        try {
            $black_list = Common::getUserBlackListInRoom($owner_id, $user->id);
            if ($black_list) return Common::apiResponse(false, __('You have been blocked by the other party'), null, 422);
        } catch (\Exception $e) {
            Log::error('Failed to check black list', ['user_id' => $user->id, 'owner_id' => $owner_id, 'error' => $e->getMessage()]);
            // Continue even if black list check fails
        }


        if (!$room) return Common::apiResponse(false, 'No room yet, please create first', null, 404);
        // if(($room->count_room_socket == 0 ) && $room->uid != $user_id && $room->pin != 1 )return Common::apiResponse(false, __('api_responses.closedRoom'), null, 402);
        if ($room->room_status == 2) {
            return Common::apiResponse(0, __('room_closed'));
        }
        $roomBlack = $room->room_black;
        if (!empty($roomBlack)) {
            $is_black = explode(',', $roomBlack);
            foreach ($is_black as $k => &$v) {
                $arr = explode("#", $v);
                $sjc = time() - (int)$arr[1];
                $rt = (int)$arr[2] - $sjc;
                $h = floor($rt / 3600);
                $r = $rt % 3600;
                $m = floor($r / 60);
                $s = $r % 60;
                if ($sjc < (int)$arr[2] && $arr[0] == $user->id) {
                    $messageBlack = __('No entry for ') . $arr[2] / 60 . __(' minutes after being kicked out of the room');
                    $remainingTime = ['remaining_time' => "$h:$m:$s"];

                    return Common::apiResponse(false, __('No entry for ') . $arr[2] / 60 . __(' minutes after being kicked out of the room'));
                }

                if ($sjc >= (int)$arr[2]) {
                    unset($is_black[$k]);
                }
            }
            $roomBlack = implode(",", $is_black);
            $this->roomRepository->updateRoomBlack($room, $roomBlack);
        }


        if ($room->room_pass &&  $owner_id != $user->id) {
            if (!$room_pass)  return Common::apiResponse(false, __('The room is locked, please enter the password'), null, 409);
            if ($room->room_pass != $room_pass) return Common::apiResponse(false, __('Password is incorrect, please re-enter'), null, 410);
        }

        $this->deleteOldRoomMic($user, $room);

        if ($user->id == $owner_id) {
            $room->is_afk = 1;
            $room->save();
            if ($room->count_room_socket == 0) {
               
                    dispatch(new SendNotificationToAllFollowers($room->uid))->onQueue('notification_heavy');
               
            }
        }


        $room_info = (new EnterRoomCollection($room, $user->id));

        $room_info = $room_info->toArray($request);


        $this->updateRoom($user->id, $owner_id, $room);
        $this->enterTheRoomCreateOrUpdate($user->id, $owner_id, $room->id);
        // $this->updateRoomVisitor($user_id, $owner_id, $room);

        //send to zego
        $user->enableSaving = false;
        $user->now_room_uid = (int)$room->id;
        $user->save();

        if (config('app.env') != "production") {
            try {
                RoomVisitor::firstOrCreate([
                    'user_id' => $user->id,
                    'room_id' => $room->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create room visitor', ['user_id' => $user->id, 'room_id' => $room->id, 'error' => $e->getMessage()]);
                // Continue even if visitor creation fails
            }
        }

        return Common::apiResponse(true, '', $room_info);
    }

    public function deleteOldRoomMic($user, $room)
    {
        RoomMicrophone::where('user_id', $user->id)->where('room_id','!=', $room->id)->delete();
        $partner = $user->lovelyRelations()
            ->with(['userOne', 'userTwo'])
            ->first()?->partner;

        CpRoomHistory::where("user_one_id", $user->id)
            ->orWhere("user_two_id", $user->id)->delete();

        $json = $this->cpMapJson([]);
        if ($partner) Common::sendToZego('SendCustomCommand', @$partner->now_room_uid, $partner->id, $json);
    }



    private function updateRoom($user_id, $owner_id, Room &$room)
    {
        // $this->updateRoomVisitors($user_id, $owner_id, $room);

        if ($room->charizma_status && ($room->charizma_timestamp  + 86400) < now()->timestamp) {
            $room->charizma_timestamp = null;
            $room->charizma_status = false;
            dispatch(new ResetCharisma($room->id));
        }
        if ($room->uid == $user_id) {
            $room->is_live = true;
        }

        $room->save();
    }
    private function enterTheRoomCreateOrUpdate($user_id, $owner_id, $room_id)
    {
        $timezone = Common::timeZone();

        EnteredRoom::query()->updateOrCreate(
            [
                'uid' => $user_id,
                'ruid' => $owner_id,
                'rid' => $room_id
            ],
            [
                'entered_at' => now($timezone)
            ]
        );
        RoomCupHelper::updateRoomVisitors($room_id);
    }


    public function makeRequestInviteRoom($user, $request)
    {

        $tokens_notfacion = [];

        $room = Room::where('uid', '=', $request->owner_id)->first();
        if (!$room) throw new Exception('room not found');

        $chatRoom = ChatRoom::BetweenUsers($user->id, $request->user_id)->first();


        if (!$chatRoom) {

            $chatRoom = ChatRoom::create([
                'user_id' => $user->id,
                'user_id2' => $request->user_id
            ]);

            $user->current_room_chat = $chatRoom->id;
            $user->save();
        }


        $user2 = User::find($request->user_id);
        if (!$user2) throw new Exception('user not found');


        $data = [
            'title' => __('I invite you to enter my room'),
            'room_owner_id' => intval($request->owner_id),
            'room_id' => intval($room->id),
            'status' => 0
        ];

        $key = env('MESSAGE_KEY');

        $message = json_encode($data);

        $chatMessageData = [
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'room_owner_id' => intval($request->owner_id),
            'room_id' => intval($room->id),
            'message' => __('I invite you to enter my room'),
            'type' => 'invite_room'
        ];

        if ($user2->online == 1 && $user2->current_room_chat == $chatRoom->id) {

            $chatMessageData['status'] = 'seen';
        } else if ($user2->online == 1) {
            $chatMessageData['status'] = 'received';
        }
        $chatMessage = ChatMessage::create($chatMessageData);

        if ($user2->is_logout != 1) {
            $tokens_notfacion[] = \DB::table('users')->where('id', $user2->id)->value('notification_id');
            $title = $user->name;
            $body = $message;
            $type = $message->type ?? 'text';
            Common::send_firebase_notification($tokens_notfacion, $title, $body, messageType: $type);
        }

        $message_resource = new ChatMessageResource($chatMessage);
        $room_resource =  new ChatRoomResourcePusher($chatRoom);
        if ($chatRoom->user_id == $user->id) {
            $chatuser = User::find($chatRoom->user_id2);
        } else {
            $chatuser = User::find($chatRoom->user_id);
        }

        try {
            event(new OpenChat($room_resource->toResponse(request())->getData()->data, $chatuser, $chatRoom));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        event(new Conversation($message_resource->toResponse(request())->getData()->data, $user2, $room_resource));

        event(new Chat($room_resource->toResponse(request())->getData()->data, $user2));

        return Common::apiResponse(1, 'تم الارسال  بنجاح');
    }




    public function enterLiveRoom($user, Request $request, $roomPass, Room $room)
    {
        $ownerId = $this->getOwnerId($request, $room);
        if (!$ownerId) {
            return Common::apiResponse(0, 'not found', null, 404);
        }

        if ($this->isUserBlocked($ownerId, $user->id)) {
            return Common::apiResponse(false, __('You have been blocked by the other party'), null, 422);
        }

        if ($error = $this->validateRoomStatus($room, $user)) {
            return $error;
        }

        if ($error = $this->checkRoomPassword($room, $roomPass, $ownerId, $user->id)) {
            return $error;
        }

        $this->handleOwnerLogic($room, $user);

        $roomInfo = $this->prepareRoomInfo($room, $user, $request);

        $this->finalizeRoomEnter($user, $ownerId, $room);

        return Common::apiResponse(true, '', $roomInfo);
    }

    private function getOwnerId(Request $request, Room $room): ?int
    {
        return $request->type === 'random'
            ? $this->roomRepository->randomOwner()
            : $room->uid;
    }

    private function isUserBlocked(int $ownerId, int $userId): bool
    {
        try {
            return Common::getUserBlackListInRoom($ownerId, $userId);
        } catch (\Exception $e) {
            Log::error('Failed to check if user is blocked', ['owner_id' => $ownerId, 'user_id' => $userId, 'error' => $e->getMessage()]);
            return false; // If check fails, assume not blocked to avoid blocking legitimate users
        }
    }

    private function validateRoomStatus(Room $room, $user)
    {
        if (!$room) {
            return Common::apiResponse(false, 'No room yet, please create first', null, 404);
        }

        if ($room->room_status == 2) {
            return Common::apiResponse(0, __('room_closed'));
        }

        // if ($this->isUserInTempBlacklist($room, $user->id)) {
        //     return Common::apiResponse(false, __('You cannot enter this room temporarily'), null, 403);
        // }

        return null;
    }

    private function isUserInTempBlacklist(Room $room, int $userId): bool
    {
        // Use new blacklist repository for cleaner, database-level checking
        $blacklistRepo = app(\App\Repositories\RoomBlacklistRepository::class);
        return $blacklistRepo->isBlacklisted($room->id, $userId);
    }

    private function checkRoomPassword(Room $room, ?string $roomPass, int $ownerId, int $userId)
    {
        if ($room->room_pass && $ownerId !== $userId) {
            if (!$roomPass) {
                return Common::apiResponse(false, __('The room is locked, please enter the password'), null, 409);
            }
            if ($room->room_pass !== $roomPass) {
                return Common::apiResponse(false, __('Password is incorrect, please re-enter'), null, 410);
            }
        }
        return null;
    }

    private function handleOwnerLogic(Room $room, $user): void
    {
        if ($user->id === $room->uid) {
            $room->is_afk = 1;
            $room->save();

            if ($room->count_room_socket == 0) {
                try {
                    dispatch(new SendNotificationToAllFollowers($room->uid))->onQueue('notification_heavy');
                } catch (\Throwable $e) {
                    Log::warning('Failed to dispatch follower notification: ' . $e->getMessage());
                }
            }
        }
    }

    private function prepareRoomInfo(Room $room, $user, Request $request): array
    {
        $roomInfo = (new EnterRoomCollection($room, $user->id))->toArray($request);
        return $roomInfo;
    }

    private function finalizeRoomEnter($user, int $ownerId, Room $room): void
    {
        $this->updateRoom($user->id, $ownerId, $room);
        $this->enterTheRoomCreateOrUpdate($user->id, $ownerId, $room->id);

        $user->enableSaving = false;
        $user->now_room_uid = $ownerId;
        $user->save();

        if (config('app.env') !== "production") {
            RoomVisitor::firstOrCreate([
                'user_id' => $user->id,
                'room_id' => $room->id,
            ]);
        }
    }
}
