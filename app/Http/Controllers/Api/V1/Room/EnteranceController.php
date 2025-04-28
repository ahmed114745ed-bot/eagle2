<?php

namespace App\Http\Controllers\Api\V1\Room;

use App\Facades\UserHandling;
use App\Models\AllGame;
use App\Models\Pk;
use App\Models\Room;
use App\Models\RoomVisitor;
use App\Models\User;
use App\Models\BoxUse;
use App\Models\Family;
use App\Models\Follow;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\LiveTime;
use App\Models\Background;
use App\Jobs\ResetCharisma;
use App\Models\EnteredRoom;
use App\Models\RoomCategory;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\EnterRoomZigoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\EditRoomRequest;
use App\Models\RequestBackgroundImage;
use App\Http\Resources\Api\V1\PkResource;
use App\Jobs\SendNotificationToAllFollowers;
use App\Repositories\Room\RoomRepoInterface;
use App\Http\Resources\Api\V1\BoxUseResource;
use App\Http\Services\ProfileRelationsService;
use App\Http\Resources\Api\V1\MiniUserResource;
use App\Http\Resources\Api\V1\RoomUserResource;
use App\Http\Resources\Api\V1\EnterRoomCollection;
use App\Tik\Services\EnteranceRoomServices;
use Illuminate\Support\Facades\Validator;
use Modules\Charizma\Http\Services\UserCharismaService;
use Modules\CP\Entities\CpRoomHistory;

class EnteranceController extends Controller
{

    protected $repo;
    protected $enteranceRoomService;

    public function __construct(RoomRepoInterface $repo, EnteranceRoomServices $enteranceRoomService)
    {
        $this->repo = $repo;
        $this->enteranceRoomService = $enteranceRoomService;
    }

    public function updateRoomCountFromPusher(Request $request)
    {
        return $this->enteranceRoomService->updateRoomCountFromPusher($request);
    }

    public function updateRoomCountFromPusher_new(Request $request)
    {
    }


    public function updateRoomCountFromZego(Request $request)
    {
        $library = Common::getConfig('library');
        if ($library == 2) return Common::apiResponse(false, 'you used pusher');
        return $this->enteranceRoomService->updateRoomCountFromZego($request);
    }

    public function updateRoomCountPusher(Request $request)
    {
        return $this->enteranceRoomService->updateRoomCountFromPusher($request);
    }

    public function libraryAgoraZego()
    {
        $agora_app_id = Common::getConfig('app_id');
        $zego_server_secret = Common::getConfig('zego_server_secret');
        $zego_app_id = Common::getConfig('zego_app_id');
        $app_sign = Common::getConfig('app_sign');
        $library = Common::getConfig('library');
        $data = [
            'agora_app_id' => $agora_app_id,
            'zego' => [
                'server_secret' => $zego_server_secret,
                'app_id' => $zego_app_id,
                'app_sign' => $app_sign,
            ],
            'library' => $library == 1 ? 'zego' : 'agora',

        ];
        return Common::apiResponse(1, '', $data);
    }

    public function checkSignature($secert, $signature, $timestamp, $nonce)
    {
        $secert = 'a23b121a64ee9fab4567a2d75d00269d';
        $signature = "e95d06c85fa9c0d296e3d0077245f9dcf5de23eb";
        $timestamp = "1711027495";
        $nonce = "7348807134281767609";

        $tmpArr = array($secert, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function updateMicrophone($room_uid, $user_id)
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

    protected function updateRoomVisitorsBasedOnEvent($event, $room, $userId)
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

    protected function handleCharismaStatusOnLogout($room, $user, $ownerId)
    {
        $userCharismaService = new UserCharismaService();
        $userCharismaService->resetUserCharisma($user->id, $room->id);
        $userDataWithCharisma = $userCharismaService->getUserResetData($room->microphone, [$user->id]);

        $message = [
            'messageContent' => [
                "message" => "updateCharisma",
                'data' => $userDataWithCharisma
            ]
        ];

        Common::sendToZego('SendCustomCommand', $room->id, $ownerId, json_encode($message));
    }

    //    public function checkSignature($signature,$timestamp,$nonce)
    //    {
    //        $secret = Common::getConf('zego_server_secret');
    //        $tempArr = [$secret, (string)$timestamp, $nonce];
    //        sort($tempArr, SORT_STRING);
    //
    //        $tmpStr = implode('', $tempArr);
    //        $calculatedSignature = sha1($tmpStr);
    //        return $signature == $calculatedSignature;
    //    }

    public function updateRoomCount(Request $request)
    {
        if (!$request->header('X-Pusher-Key') === env('PUSHER_APP_KEY')) {
            abort(403, 'Invalid Pusher webhook request');
        }
        $name = $request->events[0]['name'];
        $user_id = $request->events[0]['user_id'];

        if ($name !== 'member_added') {
            // RemoveUserFromRoomJob::dispatch( $user_id);
            $user = User::find($user_id);
            if ($user) {
                $room = Room::where('uid', $user->now_room_uid)->first();
                if ($room) {

                    $user->now_room_uid = 0;
                    $user->update();

                    $room->count_room_socket -= 1;
                    $room->update();
                }
            }
        }
        return response()->json(['status' => 'Webhook received']);
    }

    public function usersRoom(Request $request, ProfileRelationsService $profileRelationsService)
    {
        //        $user_id  = Auth::id();
        $usersIds = (array)$request->users_ids;
        $users = User::withoutAppends()->with([
            'packs' => function ($query) {
                return $query->whereIn('type', [5, 18]);
            },
            'profile',
            'UserVip',
            'dress2',
            'mangerType'
        ])
            ->whereIn('id', $usersIds)->get();

        [$senderLevels, $receivedImage] = $profileRelationsService->getLevelsSenderAndReceiver($users);
        RoomUserResource::initializeData($senderLevels, $receivedImage);
        $data = RoomUserResource::collection($users);
        return Common::apiResponse(1, '', $data);
    }

    public function usersRoomVisitor(Request $request, ProfileRelationsService $profileRelationsService)
    {
        //        $user_id  = Auth::id();
        $ownerId = @$request->owner_id;
        if (!$ownerId) return Common::apiResponse(false, __('room not found'));
        $room = Room::withoutAppends()->where('uid', $ownerId)->first();
        if (!$room) return Common::apiResponse(false, __('room not found'));
        $roomVisitors = $room->room_visitor;

        $usersIds = explode(',', $roomVisitors);
        $users = User::withoutAppends()->with([
            'packs' => function ($query) {
                return $query->whereIn('type', [5, 18]);
            },
            'profile',
            'UserVip',
            'dress2',
            'mangerType'
        ])
            ->whereIn('id', $usersIds)->get();

        [$senderLevels, $receivedImage] = $profileRelationsService->getLevelsSenderAndReceiver($users);
        RoomUserResource::initializeData($senderLevels, $receivedImage);
        $data = RoomUserResource::collection($users);
        return Common::apiResponse(1, '', $data);
    }

    public function enter_room(Request $request): JsonResponse
    {
        
        $room_pass = $request['room_pass'];
        $owner_id = $request['owner_id'];
        $user = $request->user();
        $ban = Common::ifRoomHasband($owner_id);
        if ($ban) {
            return Common::apiResponse(0, 'ban active for this room.', ['ban' => true]);
        }
        request()->default_background = \DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->limit(1)->first()->img;

        return $this->enteranceRoomService->enterRoom($user, $request, $room_pass, $owner_id);
    }

    private function updateRoom($user_id, $owner_id, Room &$room)
    {
        $this->updateRoomVisitors($user_id, $owner_id, $room);

        if ($room->charizma_status && ($room->charizma_timestamp + 86400) < now()->timestamp) {
            $room->charizma_timestamp = null;
            $room->charizma_status = false;
            dispatch(new ResetCharisma($room->id));
        }

        $room->save();
    }

    private function updateRoomVisitor($user_id, $owner_id, Room $room)
    {
        if ($user_id == $owner_id) {
            $room->room_status = 1;
            //            $room->save();
        }
        $room->count_room_socket += 1;
        $visitors = explode(',', $room->room_visitor);
        if ($visitors[0] == '') $visitors = [];
        if (!in_array($user_id, $visitors)) {
            $visitors[] = $user_id;
            $visitors = array_unique($visitors);
            $visitors = trim(implode(",", $visitors), ",");
            $room->room_visitor = $visitors;
        }
        $room->save();
    }

    //exit the room
    public function quit_room(Request $request)
    {

        if (!$request->owner_id) Common::apiResponse(false, __('api_responses.missing_owner_id'), null, 422);
        $user_id = $request->user()->id;
        $room = Room::query()->where('uid', $request->owner_id)->first();
        if ($room) {
            $room->count_room_socket -= 1;
            if ($room->count_room_socket < 0) {
                $room->count_room_socket = 0;
            }
            $room->enableSaving = false;
            $room->save();
        }
        $isToZegoCharisma = false;
        if ($room->charizma_status) {
            $userCharismaService = new UserCharismaService();
            $userCharismaService->resetUserCharisma($user_id, $room->id);
            $userDataWithCharisma = $userCharismaService->addTotalEarnedCoinsInUserRoom($room, [$user_id]);
            $isToZegoCharisma = true;
        }
        $res = Common::quit_hand($request->owner_id, $user_id);
        $visitor_ids_list = explode(',', $res);
        $user = $request->user();
        $user->now_room_uid = 0;
        $user->save();
        $this->calcTime($user_id);
        if ($isToZegoCharisma && isset($userDataWithCharisma)) {
            $ms = [
                'messageContent' => [
                    "message" => "updateCharisma",
                    'data' => $userDataWithCharisma
                ]
            ];
            $json = json_encode($ms);

            Common::sendToZego('SendCustomCommand', $room->id, $request->owner_id, $json);
        }

        $this->handleLeaveCp($user, $room);

        return Common::apiResponse(true, 'exited', ['visitor_ids_list' => $visitor_ids_list]);
    }

    public function handleLeaveCp($user, $room)
    {
        $userId = $user->id;
        $this->removeUserCpInRoom($userId);
        return $this->sendCpLovelyMessage($room, $user);
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

    public function sendCpLovelyMessage($room, $user)
    {
        $cpRoomHistories = CpRoomHistory::where("room_id", $room->id)->get(['index1', 'index2']);
        $indices = $cpRoomHistories->map(function ($history) {
            return [$history->index1, $history->index2];
        })->toArray();

        $json = $this->cpMapJson($indices);

        Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);
    }

    public function removeUserCpInRoom(mixed $userId): void
    {
        CpRoomHistory::where("user_one_id", $userId)
            ->orWhere("user_two_id", $userId)->delete();
    }

    public function out_room(Request $request)
    {
        $uid = $request->owner_id ?: 0;
        $black_id = $request->user_id ?: 0;
        $duration = $request->minutes ?: 5;
        if (!$uid || !$black_id) return Common::apiResponse(0, 'invalid data', null, 422);
        if (!Common::can_kick($black_id)) return Common::apiResponse(0, 'cant kick this user', null, 403);
        $black_list = @DB::table('rooms')->where('uid', $uid)->first()->room_black;
        $room_id = @DB::table('rooms')->where('uid', $uid)->first()->id;
        if ($black_list == null) {
            $black_list = $black_id . '#' . time() . '#' . ($duration * 60);
        } else {
            $list = explode(',', $black_list);
            $exists = false;
            foreach ($list as &$item) {
                $black = explode('#', $item);
                if ($black[0] == $black_id) {
                    $item = $black_id . '#' . time() . '#' . ($duration * 60);
                    $exists = true;
                }
            }
            if (!$exists) {
                array_push($list, $black_id . '#' . time() . '#' . ($duration * 60));
            }

            $black_list = implode(',', $list);
        }
        $result = DB::table('rooms')->where('uid', $uid)->update(['room_black' => $black_list]);

        if ($result) {
            //exit the room
            Common::quit_hand($uid, $black_id);
            $user = User::find($black_id);
            if ($user) {
                $user->now_room_uid = 0;
                $user->save();
            }
            $mc = [
                'messageContent' => [
                    'message' => 'kickout',
                    'duration' => $duration
                ]
            ];
            $json = json_encode($mc);
            $b = User::find($black_id);
            $n = 'nan';
            if ($b) {
                $n = $b->name ?: 'nan';
            }
            Common::sendToZego_4('SendCustomCommand', $room_id, $uid, $black_id, $json);
            $this->calcTime($black_id);
            Common::sendToZego_2('SendBroadcastMessage', $room_id, $uid, 'room', " تم طرد $n");
            return Common::apiResponse(1, 'success');
        } else {
            return Common::apiResponse(0, 'fail', null, 400);
        }
    }

    private function enterTheRoomCreateOrUpdate($user_id, $owner_id, $room_id)
    {
        EnteredRoom::query()->updateOrCreate(
            [
                'uid' => $user_id,
                'ruid' => $owner_id,
                'rid' => $room_id
            ],
            [
                'entered_at' => now()
            ]
        );
    }

    public function calcTime($uid)
    {
        $timer = LiveTime::query()->where('uid', $uid)->where('end_time', null)->first();
        if ($timer) {
            $hours = round((time() - $timer->start_time) / (60 * 60), 2);
            $timer->end_time = time();
            $timer->hours = $hours;
            $d = LiveTime::query()->where('uid', $uid)->whereDate('created_at', today())->where('days', '>=', 1)->exists();
            if (!$d) {
                if ($hours >= 1) {
                    $timer->days = 1;
                }
            }

            $timer->save();
        }
    }


    public function update(EditRoomRequest $request, $id)
    {
        try {
            $room = $this->repo->find($id);
            if (!$room) {
                return Common::apiResponse(false, 'Room not found', null, 404);
            }
            // return  $request->user ()->id;
            if ($room->uid != $request->user()->id && !in_array($request->user()->id, explode(',', $room->room_admin))) {
                return Common::apiResponse(false, 'not allowed', null, 422);
            }
            if ($request->room_name) {
                $room->room_name = $request->room_name;
            }

            if ($request->hasFile('room_cover')) {
                $room->room_cover = Common::upload('rooms', $request->file('room_cover'));
            }

            if ($request->free_mic) {
                $room->free_mic = $request->free_mic;
            }

            if ($request->room_intro) {
                $room->room_intro = $request->room_intro;
            }

            if ($request->type) {
                $room->type = $request->type;
                if ($request->type == 'single_live' || $request->type == 'multi_live') {
                    $room->is_live = true;
                }
            }

            if ($request->room_pass) {
                $room->room_pass = $request->room_pass;
            }

            if (!is_null($request->mode)) {
                $this->changeMode($request, $request->mode, $room);
            }

            if ($request->room_type) {
                if (!RoomCategory::query()->where('id', $request->room_type)->where('enable', 1)->exists()) return Common::apiResponse(0, 'type not found', null, 404);
                $room->room_type = $request->room_type;
            }

            if ($request->room_class) {
                if (!RoomCategory::query()->where('id', $request->room_class)->where('enable', 1)->exists()) return Common::apiResponse(0, 'class not found', null, 404);
                $room->room_type = $request->room_type;
            }
            $background_me = '';

            if ($request->room_background) {
                /*if (!Background::query ()->where ('id',$request->room_background)->where ('enable',1)->exists ()){
                    return Common::apiResponse (0,'background not found',null,404);
                }*/
                if ($request->change == 'app') {
                    $room->room_background = $request->room_background;

                    RequestBackgroundImage::query()->where('owner_room_id', $room->uid)->where('status', 1)->update(['status' => 3]);
                }
                if ($request->change == 'me') {

                    RequestBackgroundImage::query()->where('owner_room_id', $room->uid)->where('id', '!=', $request->room_background)->where('status', 1)->update(['status' => 3]);
                    $background_update = RequestBackgroundImage::where('id', $request->room_background)->first();
                    $background_update->status = 1;
                    $background_update->save();
                    $background_me = $background_update->img;
                    $room->room_background = null;
                }
            }
            //    $this->repo->save ($room);

            $room->save();
            // if($room->save ()){
            // return "ايوووه يا باشا ";
            //             }else{
            // return "لا يا باشا ";

            //             }


            $request['owner_id'] = $room->uid;
            $is_locked = false;
            if ($room->room_pass != null) {
                $is_locked = true;
            }
            $data = [
                "messageContent" => [
                    "message" => "changeBackground",
                    "imgbackground" => $room->final_room_image ?: @$background_me,
                    "roomIntro" => $room->room_intro ?: "",
                    "roomImg" => $room->room_cover ?: "",
                    "room_type" => @$room->myType->name ?: "",
                    "room_name" => @$room->room_name ?: "",
                    "is_locked" => @$is_locked ?: false,
                    "type_room" => @$room->type_room ?: "",
                    "is_live" => @$room->is_live ?: false,
                ]
            ];
            $json = json_encode($data);
            Common::sendToZego('SendCustomCommand', $room->id, $request->user()->id, $json);
            $request->is_update = true;
            // return 0;
            $room_info = (new EnterRoomCollection($room, $request->user()->id));
            $keys = Common::getConfFromKey(['app_sign', 'zego_app_id']);
            $room_info = $room_info->toArray($request);
            // $room_info['zego_keys'] = $keys->mapWithKeys(function ($item){
            //     return [$item['name'] => $item['name'] == 'zego_app_id' ? (integer)$item['value'] :$item['value']];
            // });

            return Common::apiResponse(true, '', $room_info);
        } catch (\Exception $exception) {
            return Common::apiResponse(false, 'failed', $exception->getMessage(), 400);
        }
    }

    public function changeMode($request, $currentMode, Room $room){
        $lastMode = $room->mode;

        $room->mode = $currentMode;
        $jsons = [];
        $map = [];
        if ($currentMode == '1') {
            $mode = 'party';
        } elseif ($currentMode == '2') {
            $mode = 'seats12';
        } elseif ($currentMode == '5') {
            $mode = 'cinema';
        } elseif ($currentMode == '4') {
            $mode = 'game';
            if (!$request->game_id) return Common::apiResponse(0, 'please send game_id', null, 404);
            $game = AllGame::find($request->game_id);
            if (!$game) return Common::apiResponse(false, 'this game does not exists');

            $room->game_id = $request->game_id;
            $map['game_url'] = $game->mini_url;
        } else {
            $mode = 'topCenter';
        }
        $ms = [
            'messageContent' => array_merge($map, ['message' => 'roomMode', 'mode' => $mode])
        ];
        $json = json_encode($ms);
        $jsons[] = $json;
        if ($lastMode == '3' && $currentMode != '3') {
            $jsons[] = $this->changeBackground($room, $room->uid, $this->getRoomBackground($room));
        }
        Common::sendToZego3('SendCustomCommand', $room->id, $request->user()->id, $jsons);
    }

    public function changeBackground(Room $room, int $owner_id, string $image = ''): string|false
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
        return $json;
    }

    public function getRoomBackground(?Room $room)
    {
        if ($room == null) return '';
        return $room->final_room_image;

    }

    public function updateRoomVisitors($user_id, $owner_id, Room &$room): void
    {
        //        $room->count_room_socket += 1;
        //        $visitors                = explode(',', $room->room_visitor);
        //        if ($visitors[0] == '') $visitors = [];
        //        if (!in_array($user_id, $visitors)) {
        //            $visitors[]         = $user_id;
        //            $visitors           = array_unique($visitors);
        //            $visitors           = trim(implode(",", $visitors), ",");
        //            $room->room_visitor = $visitors;
        //        }

    }

    public function invite_user(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'owner_id' => 'required|exists:rooms,uid',
        ]);

        if ($validator->fails()) {
            $errors = implode(',', $validator->errors()->all());
            return Common::apiResponse(0, $errors);
        }



        $user = $request->user();
        try {
            $send = $this->enteranceRoomService->makeRequestInviteRoom($user, $request);
            return $send;
        } catch (\Exception $th) {
            // \Log::error('Error inviting to room: ' . $th->getMessage());
            return Common::apiResponse(0,     $th->getMessage(),[],500);


        }
    }




}
