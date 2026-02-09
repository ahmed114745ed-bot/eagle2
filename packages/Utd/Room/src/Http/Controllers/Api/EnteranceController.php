<?php

namespace Utd\Room\Http\Controllers\Api;

use App\Models\AllGame;
use Utd\Room\Entities\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Models\LiveTime;
use Utd\Room\Entities\RoomCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EditRoomRequest;
use Utd\Room\Entities\RequestBackgroundImage;
use Utd\Room\Repositories\RoomRepoInterface;
use Utd\Room\Repositories\BackgroundRepository;
use Utd\Room\Http\Resources\EnterRoomCollection;
use App\Http\Services\EnterRoomService;
use Utd\Room\Services\EntranceRoomService;
use Illuminate\Support\Facades\Validator;
use App\Contracts\UserCharismaServiceContract;

class EnteranceController extends Controller
{

    protected $repo;
    protected $enteranceRoomService;
    protected $backgroundRepo;

    public function __construct(RoomRepoInterface $repo, EntranceRoomService $enteranceRoomService, BackgroundRepository $backgroundRepo)
    {
        $this->repo = $repo;
        $this->enteranceRoomService = $enteranceRoomService;
        $this->backgroundRepo = $backgroundRepo;
    }

    public function updateRoomCountFromPusher(Request $request)
    {
        return $this->enteranceRoomService->updateRoomCountFromPusher($request);
    }

    public function updateRoomCountFromPusher_new(Request $request) {}

    public function updateRoomCountFromZego2(Request $request)
    {
        return $this->enteranceRoomService->updateRoomCountFromZego2($request);
    }

    public function libraryAgoraZego()
    {
        $agora_app_id = Common::getConfig('app_id');
        $zego_server_secret = Common::zegoData('zego_server_secret');
        $zego_app_id = Common::zegoData('zego_app_id');
        $app_sign = Common::zegoData('zego_app_sign');
        $library = Common::getConfig('video_library');
        $liveLibrary = (int) Common::getConfig('live_library');
        $zego_filter_enabled = Common::getConfig('zego_filter_enabled');
        $is_auto_preview = (int) Common::getConfig('is_auto_preview');


        $libraries = ['agora', 'zego', 'tencent', 'Utd zego'];
        $liveTypes = ['RTC', 'CDN', 'L3'];

        $data = [
            'agora_app_id' => $agora_app_id,
            'zego' => [
                'server_secret' => $zego_server_secret,
                'app_id' => $zego_app_id,
                'app_sign' => $app_sign,
                'filter' => $zego_filter_enabled == 1 ? true : false,
                'live_type' => $liveTypes[@$liveLibrary ?? 0]
            ],
            'library' => $libraries[$library],
            'is_auto_preview' => $is_auto_preview == 1 ? true : false,


        ];
        return Common::apiResponse(1, '', $data);
    }

    public function updateMicrophone($room_uid, $user_id)
    {
        $user = User::query()->find($user_id);
        if (!$user) return;
        $result = Common::go_microphone_hand($room_uid, $user_id);

        $room = Room::query()->where('uid', $room_uid)->first();

        if (!$room) return;
        if ($result) {
            app(UserCharismaServiceContract::class)->RemoveUserRoomWhenLeaveMic($user_id, $room->id);
        }
    }

    /**
     * @throws \Exception
     */
    public function enter_room(Request $request, EnterRoomService $enterRoomServices): JsonResponse
    {
        $user = $request->user();
        $zego_feature = \Cache::rememberForever('zego_feature', function () {
            return \DB::table('settings')->where('key', 'zego_feature')->value('value');
        });

        if ($zego_feature && $zego_feature == 1) {
            throw new \Exception(__('Zego Feature is Disabled, Contact the administration'));
        }
        $user     = $request->user();
        $roomId   = $request->input('room_id');
        $roomPass = $request->input('room_pass');

        if (!$roomId) {
            return $this->errorResponse(__('Please provide a room id.'), 422);
        }

        $room = $this->findRoom($roomId);
        $room->load('microphones.user.profile');
        if (!$room) {
            return $this->errorResponse(__('Room not found.'), 404);
        }

        if ($banResponse = $enterRoomServices->checkRoomBan($user, $room)) {
            return $banResponse;
        }

        $type  =  $room->type;


        $this->setDefaultBackground();

        return $this->handleRoomType($type, $user, $request, $roomPass, $room);
    }


    private function errorResponse(string $message, int $code, array $extra = []): JsonResponse
    {
        return Common::apiResponse(0, $message, $extra ?: null, $code);
    }

    private function findRoom(int $roomId): ?Room
    {
        return Room::find($roomId);
    }

    private function setDefaultBackground(): void
    {
        request()->default_background = $this->backgroundRepo->getDefaultImage();
    }

    private function handleRoomType(?string $type, $user, Request $request, $roomPass, Room $room): JsonResponse
    {
        return match ($type) {
            'audio' => $this->enteranceRoomService->enterRoom($user, $request, $roomPass, $room),
            'live'  => $this->enteranceRoomService->enterLiveRoom($user, $request, $roomPass, $room),
            default => $this->errorResponse(__('Invalid room type. Allowed types: audio, live.'), 422),
        };
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
            $user = $request->user();
            $type = $request->get('type', 'audio');

            $room = $this->repo->findByType($id, $type);
            if (!$room) {
                return Common::apiResponse(false, 'Room not found', null, 404);
            }
            if ($user->id != $room->uid) return Common::apiResponse(0, __('you don not have permission'), null, 404);

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

            if ($type) {
                $room->type = $type;
                if ($request->type == 'live') {
                    $room->is_live = true;
                } else {
                    $room->is_live = false;
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
            $room = Room::find($room->id);

            $request['owner_id'] = $room->uid;
            $is_locked = false;
            if ($room->room_pass != null) {
                $is_locked = true;
            }
            $data = [
                "messageContent" => [
                    "message" => "changeBackground",
                    "imgbackground" => $background_me ? @$background_me : $room->final_room_image,
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

    public function changeMode($request, $currentMode, Room $room)
    {
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

    public function invite_user(Request $request)
    {

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
            return Common::apiResponse(0,     $th->getMessage(), [], 500);
        }
    }
}
