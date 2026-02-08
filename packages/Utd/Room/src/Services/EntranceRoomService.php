<?php

namespace Utd\Room\Services;

use Utd\Room\Entities\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Jobs\ResetCharisma;
use Utd\Room\Entities\EnteredRoom;
use Utd\Room\Entities\RoomVisitor;
use Exception;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Utd\Room\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use App\Jobs\SendNotificationToAllFollowers;
use Utd\Room\Http\Resources\EnterRoomCollection;
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
use Modules\TaskStream\Services\TaskStreamService;
use Utd\Room\Entities\TotalRoomGift;
use Carbon\Carbon;
use App\Contracts\EnteranceRoomContract;

class EntranceRoomService implements EnteranceRoomContract
{
    protected $roomRepository;
    protected $userRepository;
    public function __construct(RoomRepository $roomRepository, UserRepository $userRepository)
    {
        $this->roomRepository = $roomRepository;
        $this->userRepository = $userRepository;
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

        /** @var Room $room */
        $room = Room::select(['id', 'uid', 'count_room_socket', 'room_visitor', 'charizma_status', 'microphone'])->find($roomId);
        $user = User::find($userId);

        if (!$room || !$user) {
            return response()->json(['status' => 'Webhook received but room or user not found']);
        }

        $visitors = $this->updateRoomVisitorsBasedOnEvent($event, $room, $user->id);

        if ($event == 'room_login') {
            $this->addUserToVisitors($room->id, $user->id);
            $user->now_room_uid = $room->id;
        } elseif ($event == 'room_logout' && $room->uid == $user->now_room_uid) {
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

        return response()->json(['status' => 'Webhook processed successfully']);
    }

    public function updateRoomCountFromZego2(Request $request)
    {
        $event = $request->event;
        $roomId = $request->room_id;
        $userId = $request->user_account;

        /** @var Room $room */
        $room = Room::select(['id', 'uid', 'count_room_socket', 'room_visitor', 'charizma_status', 'microphone'])->find($roomId);
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
        } elseif ($event == 'room_logout' && $room->uid == $user->now_room_uid) {
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
        return json_encode($ms);
    }

    private function addUserToVisitors(int $roomId, int $userId)
    {
        RoomVisitor::query()->where(['user_id' => $userId])->delete();
        RoomVisitor::query()->create(['user_id' => $userId, 'room_id' => $roomId]);
    }

    private function removeUserToVisitors(int $roomId, int $userId)
    {
        RoomVisitor::query()->where(['user_id' => $userId, 'room_id' => $roomId])->delete();
    }

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

    private function updateRoomVisitorsBasedOnEvent2($event, $room, $userId)
    {
        $visitors = $room->room_visitor ? explode(',', $room->room_visitor) : [];

        if ($event == 'room_login' && !in_array($userId, $visitors)) {
            $visitors[] = $userId;
        } elseif ($event == 'room_logout') {
            UserHandling::calcTime($userId);
            $this->updateMicrophone2($room->uid, $userId);
            $visitors = array_diff($visitors, [$userId]);
        }

        return array_values(array_unique($visitors));
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
        $result = Common::go_microphone_hand($room_uid, $user_id);

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
        $result = Common::go_microphone_hand_2($room_uid, $user_id);

        $room = Room::query()->where('uid', $room_uid)->first();

        if (!$room) return;
        if ($result) {
            (new UserCharismaService())->RemoveUserRoomWhenLeaveMic($user_id, $room->id);
        }
    }

    ///////////////////////////////

    public function enterRoom($user, $request, $room_pass, $room)
    {
        $owner_id = $room->uid;
        if ($request->sub_type == 'random') {
            $owner_id = $this->roomRepository->randomOwner();
        }

        if (!$owner_id) return Common::apiResponse(0, 'not found', null, 404);

        $black_list = Common::getUserBlackListInRoom($owner_id, $user->id);
        if ($black_list) return Common::apiResponse(false, __('You have been blocked by the other party'), null, 422);

        if (!$room) return Common::apiResponse(false, 'No room yet, please create first', null, 404);

        if ($room->room_status == 2) {
            return Common::apiResponse(0, __('room_closed'));
        }

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
                    return Common::apiResponse(false, __('No entry for ') . $arr[2] / 60 . __(' minutes after being kicked out of the room'));
                }

                if ($sjc >= $arr[2]) {
                    unset($is_black[$k]);
                }
            }
            $roomBlack = implode(",", $is_black);
            $this->roomRepository->updateRoomBlack($room, $roomBlack);
        }

        if ($room->room_pass && $owner_id != $user->id) {
            if (!$room_pass) return Common::apiResponse(false, __('The room is locked, please enter the password'), null, 409);
            if ($room->room_pass != $room_pass) return Common::apiResponse(false, __('Password is incorrect, please re-enter'), null, 410);
        }

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

        $user->enableSaving = false;
        $user->now_room_uid = (int)$room->id;
        $user->save();

        if (config('app.env') != "production") {
            RoomVisitor::firstOrCreate([
                'user_id' => $user->id,
                'room_id' => $room->id,
            ]);
        }

        return Common::apiResponse(true, '', $room_info);
    }

    private function updateRoom($user_id, $owner_id, Room &$room)
    {
        if ($room->charizma_status && ($room->charizma_timestamp + 86400) < now()->timestamp) {
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
        $this->updateRoomVisitors($room_id);
    }

    private function updateRoomVisitors(int $roomId): void
    {
        $timezone = Common::timeZone();
        $today    = Carbon::now($timezone)->startOfDay();
        $tomorrow = (clone $today)->endOfDay();

        $uniqueVisitors = EnteredRoom::query()
            ->where('rid', $roomId)
            ->whereBetween('entered_at', [$today, $tomorrow])
            ->distinct('uid')
            ->count('uid');

        $gift = TotalRoomGift::where('room_id', $roomId)
            ->whereBetween('created_at', [$today, $tomorrow])
            ->first();

        if ($gift) {
            $gift->update(['number_of_visitors' => $uniqueVisitors]);
        } else {
            TotalRoomGift::create([
                'room_id'            => $roomId,
                'current_total'      => 0,
                'number_of_visitors' => $uniqueVisitors,
            ]);
        }
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
        } elseif ($user2->online == 1) {
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
        $room_resource = new ChatRoomResourcePusher($chatRoom);
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

        return Common::apiResponse(1, 'تم الارسال بنجاح');
    }

    public function enterLiveRoom($user, Request $request, $roomPass, $room)
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
        return Common::getUserBlackListInRoom($ownerId, $userId);
    }

    private function validateRoomStatus(Room $room, $user)
    {
        if (!$room) {
            return Common::apiResponse(false, 'No room yet, please create first', null, 404);
        }

        if ($room->room_status == 2) {
            return Common::apiResponse(0, __('room_closed'));
        }

        return null;
    }

    private function isUserInTempBlacklist(Room $room, int $userId): bool
    {
        if (empty($room->room_black)) {
            return false;
        }

        $isBlack = explode(',', $room->room_black);
        foreach ($isBlack as $k => &$v) {
            $arr = explode("#", $v);
            $sjc = time() - $arr[1];
            if ($sjc < $arr[2] && $arr[0] == $userId) {
                return true;
            }
            if ($sjc >= $arr[2]) {
                unset($isBlack[$k]);
            }
        }

        $room->room_black = implode(",", $isBlack);
        $this->roomRepository->updateRoomBlack($room, $room->room_black);

        return false;
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
                dispatch(new SendNotificationToAllFollowers($room->uid))->onQueue('notification_heavy');
            }
        }
    }

    private function prepareRoomInfo(Room $room, $user, Request $request): array
    {
        return (new EnterRoomCollection($room, $user->id))->toArray($request);
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
