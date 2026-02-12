<?php

namespace Utd\Room\Http\Controllers\Api;

use App\Contracts\UserAchievementContract;
use App\Contracts\UserCharismaServiceContract;
use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Room\CommentRequest;
use App\Http\Resources\Api\V1\RoomAdminsResource;
use App\Http\Resources\Api\V1\RoomResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Services\ProfileRelationsService;
use App\Jobs\EnterRoomZigoRequest;
use App\Models\KickRecord;
use App\Models\LiveTime;
use App\Models\User;
use App\Support\PackageHelper;
use App\Traits\MultiQueryPagination;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Utd\CP\Entities\CpRoomHistory;
use Utd\LuckyBox\Entities\BoxUse;
use Utd\LuckyBox\Http\Resources\BoxUseResource;
use Utd\Room\Classes\RoomComments;
use Utd\Room\Entities\EnteredRoom;
use Utd\Room\Entities\Room;
use Utd\Room\Http\Resources\EnterRoomCollection;
use Utd\Room\Http\Resources\GiftRoomResource;
use Utd\Room\Http\Resources\RoomCountriesResource;
use Utd\Room\Http\Resources\RoomDetailsResource;
use Utd\Room\Http\Resources\RoomVisitorsResource;
use Utd\Room\Repositories\BackgroundRepository;
use Utd\Room\Repositories\RoomCategoryRepository;
use Utd\Room\Repositories\RoomRepoInterface;
use Utd\Room\Services\RoomRepoService;
use Utd\RoomBoom\Entities\RoomBoom;
use Utd\RoomBoom\Transformers\RoomBoomResource;

class RoomController extends Controller
{
    use MultiQueryPagination;

    protected $repo;

    protected $roomService;

    protected $backgroundRepo;

    protected $categoryRepo;

    public function __construct(
        RoomRepoInterface $repo,
        RoomRepoService $roomService,
        BackgroundRepository $backgroundRepo,
        RoomCategoryRepository $categoryRepo,
    ) {
        $this->repo = $repo;
        $this->roomService = $roomService;
        $this->backgroundRepo = $backgroundRepo;
        $this->categoryRepo = $categoryRepo;
    }

    public function sendPrivateComment(Request $request, int $ownerId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required',
            'to_user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, implode(' , ', $validator->errors()->all()), $validator->errors(), 422);
        }

        // user id how send and recieved this message
        $fromUser = Auth::user();
        $fromUserId = $fromUser->id;
        $toUserId = $request->to_user_id;
        $message = $request->message;
        try {
            [$toUser, $price] = $this->roomService->privateComment($toUserId, $message, $ownerId, $fromUser);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }

        return Common::apiResponse(true, 'success', [
            'message' => (@$toUser->name ?? 'name').': '.$message,
            'price' => $price,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
        ]);
    }

    public function index(Request $request)
    {
        request()->default_background = $this->backgroundRepo->getDefaultImage();
        $rooms = $this->roomService->getAllRooms($request);

        return Common::apiResponse(true, '', RoomResource::collection($rooms), 200);
    }

    public function mine(Request $request)
    {
        $user_id = request('user_id') ?? Auth::user()->id;
        request()->default_background = $this->backgroundRepo->getDefaultImage();
        $rooms = $this->roomService->getAllMine($request, $user_id);

        return Common::apiResponse(true, '', $rooms, 200);
    }

    public function userRoom($id, Request $request)
    {

        request()->default_background = $this->backgroundRepo->getDefaultImage();
        $rooms = $this->roomService->getUserRooms($request, $id);

        return Common::apiResponse(true, '', $rooms, 200);
    }

    public function getAllLiveRooms(Request $request)
    {
        request()->default_background = $this->backgroundRepo->getDefaultImage();
        $rooms = $this->roomService->getAllLiveRooms($request);

        return Common::apiResponse(true, '', $rooms, 200);
    }

    public function room_countries()
    {
        $data = $this->roomService->index2();

        return RoomCountriesResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {

        $request['show'] = true;
        $request['numid'] = rand(111111, 999999);
        $user = $request->user();

        try {
            $roomType = $request->type ?? 'audio';

            if (! in_array($roomType, ['audio', 'live'])) {
                return Common::apiResponse(false, 'Room type not matched', null, 400);
            }

            $room = $this->roomService->findRoomUserByType($user->id, $roomType);
            if ($room) {
                return Common::apiResponse(true, "You already have a room of type {$roomType}", new RoomResource($room), 200);
            }

            $room = $this->roomService->create($request, $user);

            return Common::apiResponse(true, 'created', new RoomResource($room), 200);
        } catch (Exception $exception) {
            return Common::apiResponse(false, $exception->getMessage(), null, 400);
        }
    }

    public function extraRoomData($owner_id): JsonResponse
    {
        $room = $this->roomService->findAudioRoomUser($owner_id);
        if (! $room) {
            return Common::apiResponse(false, 'No Room Founded');
        }

        $tz = getTimezone();
        $today = Carbon::today($tz);

        $openBoom = null;
        if (PackageHelper::isInstalled('roomBoom')) {
            $openBoom = RoomBoom::whereHas('totalRoomGift', function ($q) use ($room) {
                $q->where('room_id', $room->id);
            })
                ->whereNull('ended_at')
                ->whereNotNull('started_at')
                ->whereDate('started_at', $today)
                ->first();
        }

        $collections = [
            'charisma' => $this->roomCharisma($owner_id),
            'achievements' => $this->achievementLevels($owner_id),
            'boxes' => PackageHelper::isInstalled('luckyBox') ? BoxUseResource::collection($this->getBoxes($owner_id, Auth::id())) : [],
            'open_boom' => $openBoom ? new RoomBoomResource($openBoom) : null,
        ];

        return Common::apiResponse(true, 'successfully', $collections);
    }

    public function extraDataRoom(Request $request)
    {
        $roomId = $request->room_id;
        $room = $roomId
            ? Room::find($roomId)
            : Room::where('uid', $request->owner_id)->where('type', 'audio')->first();
        if (! $room) {
            return Common::apiResponse(false, 'No Room Founded');
        }
        $owner_id = $room->uid;
        $tz = getTimezone();
        $today = Carbon::today($tz);

        $openBoom = null;
        if (PackageHelper::isInstalled('roomBoom')) {
            $openBoom = RoomBoom::whereHas('totalRoomGift', function ($q) use ($room) {
                $q->where('room_id', $room->id);
            })
                ->whereNull('ended_at')
                ->whereNotNull('started_at')
                ->whereDate('started_at', $today)
                ->first();
        }

        $collections = [
            'charisma' => $this->roomCharisma($room->id),
            'achievements' => $this->achievementLevels($owner_id),
            'boxes' => PackageHelper::isInstalled('luckyBox') ? BoxUseResource::collection($this->getBoxes($room->id, Auth::id())) : [],
            'open_boom' => $openBoom ? new RoomBoomResource($openBoom) : null,
        ];

        return Common::apiResponse(true, 'successfully', $collections);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $request['show'] = true;
        $room = $this->roomService->findRoom($id);
        //        $room->load('microphones');
        if (! $room) {
            return Common::apiResponse(0, 'not found', null, 404);
        }

        return Common::apiResponse(true, '', new RoomResource($room), 200);
    }

    public function getAdmins(Request $request)
    {
        // if (!$request->owner_id && !$request->room_id) return Common::apiResponse(0, 'missing params', null, 422);
        try {
            $admins = $this->roomService->roomAdmins($request->id);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }

        $data = RoomAdminsResource::collection($admins);

        return Common::apiResponse(1, '', $data, 200);
    }

    public function quit_room_2(Request $request)
    {
        if (! $request->owner_id && ! $request->room_id) {

            return Common::apiResponse(false, __('missing parameter'), null, 422);
        }
        try {
            $user = $request->user();
            [$visitorIdsList, $isToZegoCharisma, $userDataWithCharisma, $roomId] = $this->roomService->quiteRoom2($request->owner_id, $user, $request->room_id);
            if ($isToZegoCharisma && isset($userDataWithCharisma)) {
                $ms = [
                    'messageContent' => [
                        'message' => 'updateCharisma',
                        'data' => $userDataWithCharisma,
                    ],
                ];
                $json = json_encode($ms);

                Common::sendToZego('SendCustomCommand', $roomId, $request->owner_id, $json);
            }
            $this->handleLeaveCp($user, $roomId);
            $room = Room::find($roomId);
            if ($user->id === $room->uid) {
                $room->is_afk = 0;
                $room->save();
            }
            $this->updateMicrophone2($room->uid, $user->id);

            return Common::apiResponse(true, 'exited', ['visitor_ids_list' => $visitorIdsList]);
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function handleLeaveCp($user, $roomId)
    {
        $userId = $user->id;
        $this->removeUserCpInRoom($userId);

        return $this->sendCpLovelyMessage($roomId, $user);
    }

    public function removeUserCpInRoom(mixed $userId): void
    {
        if (PackageHelper::isInstalled('cp')) {
            CpRoomHistory::where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId)->delete();
        }
    }

    public function sendCpLovelyMessage($roomId, $user)
    {
        $indices = [];
        if (PackageHelper::isInstalled('cp')) {
            $cpRoomHistories = CpRoomHistory::where('room_id', $roomId)->get(['index1', 'index2']);
            $indices = $cpRoomHistories->map(function ($history) {
                return [$history->index1, $history->index2];
            })->toArray();
        }

        $json = $this->cpMapJson($indices);

        Common::sendToZego('SendCustomCommand', $roomId, $user->id, $json);
    }

    public function cpMapJson($indices): string|false
    {
        $ms = [
            'messageContent' => [
                'message' => 'cpLovelyZego',
                'data' => $indices,
            ],
        ];
        $json = json_encode($ms);

        return $json;
    }

    public function calcTime($uid)
    {
        // case 1 : up_mic and go_mic in the same day
        $user = User::find($uid);
        $timer =
            LiveTime::query()->where('uid', $uid)->whereDate('created_at', today())->where('end_time', null)->orderByDesc('id')->first();
        if ($timer) {
            $hours = round((time() - $timer->start_time) / (60 * 60), 2);
            $timer->end_time = time();
            $timer->hours = $hours;
            $timer->save();
            // $user_day = UserDay::where('user_id', $uid)->whereDate('created_at', today())->first();
            $user_hours =
                LiveTime::query()->where('uid', $user->id)->whereYear('created_at', '=', Carbon::now()->year)->whereMonth('created_at', '=', Carbon::now()->month)->whereDay('created_at', '=', Carbon::now()->day)->sum('hours');

            $hours = (int) $user_hours;

            if ($hours >= 1 && $user->today_days === 0) {
                DB::statement('
                UPDATE users
                SET today_days = 1
                WHERE id = :id
            ', ['id' => $user->id]);
            }
        }
    }

    public function getRoomUsers(Request $request, ProfileRelationsService $profileRelationsService)
    {
        $uid = $request->owner_id;
        try {
            [$allData, $roomAdminActive] = $this->roomService->roomUsers($request);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }

        [$senderLevels, $receivedImage] = $profileRelationsService->getLevelsSenderAndReceiver($allData);
        RoomVisitorsResource::initializeData($senderLevels, $receivedImage, $roomAdminActive, $uid);
        $data = RoomVisitorsResource::collection($allData);

        return Common::apiResponse(1, '', $data);
    }

    // mic sequence list
    public function enter_room(Request $request)
    {
        $room_pass = $request['room_pass'];
        $owner_id = $request['owner_id'];

        if ($request->type === 'random') {
            $owner_id = Room::query()->where('room_status', 1)->where('uid', '!=', null)->where(function ($q) {
                $q->where('count_room_socket', '!=', 0)->orWhere('is_afk', 1);
            })->pluck('uid')->random();
        }

        $user = $request->user();
        $user_id = $user->id;

        // if owner id not path throw error
        if (! $owner_id) {
            return Common::apiResponse(0, 'not found', null, 404);
        }

        // check if this user in black-list
        $black_list = Common::getUserBlackList($owner_id);
        if (in_array($user_id, $black_list)) {
            return Common::apiResponse(false, __('You have been blocked by the other party'), null, 423);
        }

        // get room by owner_id
        $room = Room::query()->where('uid', $owner_id)->with(['owner', 'roomCategory', 'family'])->first();

        if (! $room) {
            return Common::apiResponse(false, 'No room yet, please create first', null, 404);
        }

        if ($room->room_pass && $owner_id !== $user_id && ! $request->ignorePassword) {
            if (! $room_pass) {
                return Common::apiResponse(false, __('The room is locked, please enter the password'), null, 409);
            }
            if ($room->room_pass !== $room_pass) {
                return Common::apiResponse(false, __('Password is incorrect, please re-enter'), null, 410);
            }
        }

        if (! $request->is_update) {
            if ($request->sendToZego !== 'no') {
                dispatch(new EnterRoomZigoRequest($user, $room->id, $request->have_vip));
            }
        }
        //        $this->getRoomTwoLastPk($room->id);
        $room_info = (new EnterRoomCollection($room, $user_id));

        $this->enterTheRoomCreateOrUpdate($user_id, $owner_id, $room->id);

        // send to zego
        $user->enableSaving = false;
        $user->now_room_uid = (int) $owner_id;
        $user->save();

        // Replace this line with your existing return statement
        return Common::apiResponse(true, '', $room_info);
    }

    // kick out of the room
    public function out_room(Request $request)
    {
        $uid = $request->owner_id ?: 0;
        $roomId = $request->room_id;
        $black_id = $request->user_id ?: 0;
        $duration = $request->minutes ?: 5;
        // if vip 8 not allawed to kickout

        if ((! $uid && ! $roomId) || ! $black_id) {
            return Common::apiResponse(0, 'invalid data', null, 422);
        }
        if (Common::pack_get(9, $black_id)) {
            return Common::apiResponse(0, 'cant kick this user', null, 422);
        }
        //        if (!Common::can_kick ($black_id)) return Common::apiResponse (0,'cant kick this user',null,403);
        $room = $roomId
            ? Room::find($roomId)
            : Room::where('uid', $uid)->where('type', 'audio')->first();
        if (! $room) {
            return Common::apiResponse(0, 'room not found', null, 422);
        }
        $uid = $room->uid;
        $black_list = @$room->room_black;
        $room_id = @$room->id;
        if ($black_list === null) {
            $black_list = $black_id.'#'.time().'#'.($duration * 60);
        } else {
            $list = explode(',', $black_list);
            $exists = false;
            foreach ($list as &$item) {
                $black = explode('#', $item);
                if ($black[0] === $black_id) {
                    $item = $black_id.'#'.time().'#'.($duration * 60);
                    $exists = true;
                }
            }
            if (! $exists) {
                $list[] = $black_id.'#'.time().'#'.($duration * 60);
            }

            $black_list = implode(',', $list);
        }
        $result = $this->repo->updateByUid($uid, ['room_black' => $black_list]);

        if ($result) {
            // exit the room
            Common::quit_hand_2($uid, $black_id);
            $user = User::find($black_id);
            if ($user) {
                $user->now_room_uid = 0;
                $user->save();
            }
            $mc = [
                'messageContent' => [
                    'message' => 'kickout',
                    'duration' => $duration,
                ],
            ];
            $json = json_encode($mc);
            $b = User::find($black_id);
            $n = 'nan';
            if ($b) {
                $n = $b->name ?: 'nan';
            }

            // record kicked user
            $user_request = $request->user();
            KickRecord::create([
                'kicked_user_id' => $user_request->id,
                'user_id' => $black_id,
                'room_id' => $room_id,
            ]);

            Common::sendToZego_4('SendCustomCommand', $room_id, $uid, $black_id, $json);
            $this->calcTime($black_id);
            $message = __('api.blockRoom', ['name' => $b->name, 'actionName' => $request->user()->name, 'duration' => $duration], 'ar');

            Common::sendToZego_2('SendBroadcastMessage', $room_id, $uid, 'room', $message);

            return Common::apiResponse(1, 'success');
        }

        return Common::apiResponse(0, 'fail', null, 400);

    }

    public function room_type()
    {
        $data = $this->categoryRepo->getEnabledParentCategories();

        return Common::apiResponse(1, '', $data);
    }

    // set as admin
    public function is_admin(Request $request)
    {
        $uid = $request->owner_id;
        $admin_id = $request->user_id;
        $roomId = $request->room_id;
        if ((! $uid || ! $roomId) && ! $admin_id) {
            return Common::apiResponse(0, 'invalid data', null, 422);
        }

        $room = $roomId
            ? Room::find($roomId)
            : Room::where('uid', $uid)->where('type', 'audio')->first();

        if (! $room) {
            return Common::apiResponse(0, 'Room not exist', null, 422);
        }
        $uid = $room->uid;
        if ($room->uid === $admin_id) {
            return Common::apiResponse(0, 'invalid data', null, 422);
        }
        $roomVisitor = $room->room_visitor;
        $vis_arr = ! $roomVisitor ? [] : explode(',', $roomVisitor);
        if (! in_array($admin_id, $vis_arr)) {
            return Common::apiResponse(0, 'This user is not in this room', null, 404);
        }

        $roomAdmin = $room->room_admin;
        $roomMax = $room->total_admins;
        $adm_arr = ($roomAdmin === '') ? [] : explode(',', trim($roomAdmin));
        if (count($adm_arr) > 0 && $adm_arr[0] === '') {
            unset($adm_arr[0]);
        }
        $adm_arr = array_unique($adm_arr);

        if (in_array($admin_id, $adm_arr)) {
            return Common::apiResponse(0, 'This user is already an administrator, please do not repeat the settings', null, 444);
        }
        $configMaxRoom = Common::getConfig('max_room_admin') ?? 4;

        if (count($adm_arr) === ($roomMax >= $configMaxRoom ? $roomMax : $configMaxRoom)) {
            return Common::apiResponse(0, 'room manager is full', null, 404);
        }

        $adm_arr = array_merge($adm_arr, [$admin_id]);
        $str = implode(',', $adm_arr);

        $res = $room->update(['room_admin' => $str]);
        $adm_arr = explode(',', $room->room_admin) ?? [];
        $a = User::find($admin_id);
        $n = 'nan';
        if ($a) {
            $n = $a->name ?: 'nan';
        }
        //  Common::sendToZego_2('SendBroadcastMessage', $room->id, $uid, 'room', " اصبح ادمن $n");
        $ms = [
            'messageContent' => [
                'message' => 'updateAdmins',
                'admins' => array_values($adm_arr),
            ],
        ];

        if ($res) {

            $resu = Common::sendToZego('SendCustomCommand', $room->id, $uid, json_encode($ms));

            return Common::apiResponse(1, 'Set administrator successfully', $adm_arr, 200);
        }

        return Common::apiResponse(0, 'Failed to set administrator', null, 400);

    }

    // cancel manager
    public function remove_admin(Request $request)
    {
        $user = $request->user();
        $uid = $request->owner_id;
        $admin_id = $request->user_id;
        $roomId = $request->room_id;
        if ((! $uid && ! $roomId) || ! $admin_id) {
            return Common::apiResponse(0, 'invalid data', null, 422);
        }
        $room = $roomId
            ? Room::find($roomId)
            : Room::where('uid', $uid)->where('type', 'audio')->first();
        if (! $room) {
            return Common::apiResponse(0, 'room not found', null, 422);
        }
        $uid = $room->uid;
        if ($user->id !== $room->uid) {
            return Common::apiResponse(0, __('you don not have permission'), null, 404);
        }

        $roomAdmin = $room->room_admin;
        $adm_arr = ! $roomAdmin ? [] : explode(',', $roomAdmin);
        if (! in_array($admin_id, $adm_arr)) {
            return Common::apiResponse(0, 'This user is not an administrator of this room', null, 404);
        }
        $key = array_search($admin_id, $adm_arr);
        unset($adm_arr[$key]);
        $str = implode(',', $adm_arr);
        $res = $room->update(['room_admin' => $str]);
        $adm_arr = explode(',', $room->room_admin) ?? [];

        $a = User::find($admin_id);
        $n = 'nan';
        if ($a) {
            $n = $a->name ?: 'nan';
        }
        // Common::sendToZego_2('SendBroadcastMessage', $room->id, $uid, 'room', "  لم يعد هذا المستخدم ادمن فى هذة الغرفه  $n");
        $ms = [
            'messageContent' => [
                'message' => 'updateAdmins',
                'admins' => array_values($adm_arr),
            ],
        ];
        $resu = Common::sendToZego('SendCustomCommand', $room->id, $uid, json_encode($ms));
        if ($res) {
            return Common::apiResponse(1, 'Cancel administrator successfully', $adm_arr, 200);
        }

        return Common::apiResponse(0, 'Failed to cancel administrator', null, 400);

    }

    public function removeRoomPass(Request $request)
    {
        $room = $this->roomService->changePasswordRoom($request->owner_id, $request->room_id);

        $data = [
            'messageContent' => [
                'message' => 'changeBackground',
                'imgbackground' => $room->final_room_image ?? '',
                'roomIntro' => $room->room_intro ?? '',
                'roomImg' => $room->room_cover ?? '',
                // "room_type" => @$room->myType->name ?? "",
                'room_type' => app()->getLocale() === 'ar' ? @$room->roomCategory?->name ?? @$room->roomCategory?->name_en : @$room->roomCategory?->name_en ?? @$room->roomCategory?->name,
                'room_name' => @$room->room_name ?? '',
                'is_locked' => false,

            ],
        ];
        $json = json_encode($data);
        Common::sendToZego('SendCustomCommand', $room->id, $request->user()->id, $json);

        return Common::apiResponse(1, 'success');
    }

    public function firstOfRoom(Request $request)
    {
        $OwnerId = $request->owner_id;
        if (! $OwnerId) {
            return Common::apiResponse(0, 'missing param', null, 422);
        }
        $firstRoomOwner = $this->roomService->getFirstRoomOwner($OwnerId);

        return Common::apiResponse(1, '', ['user' => new UserResource($firstRoomOwner->sender), 'total' => (int) $firstRoomOwner->total], 200);
    }

    public function changeMode(Request $request)
    {
        $currentMode = $request->mode;
        if ($currentMode === null || (! $request->owner_id && ! $request->room_id)) {
            return Common::apiResponse(0, 'missing param', null, 422);
        }

        return $this->roomService->changeMode($request, $currentMode);
    }

    public function changeMicMode(Request $request)
    {
        $currentMode = $request->mode;
        if ($currentMode === null || ! $request->owner_id) {
            return Common::apiResponse(0, 'missing param', null, 422);
        }

        return $this->roomService->changeModeMic($request, $currentMode);
    }

    /**
     * @throws ValidationException
     */
    public function sendComment(CommentRequest $request): JsonResponse
    {
        try {
            (new RoomComments($this->repo))->sendComments($request->user(), $request->validated());
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }

        return Common::apiResponse(true, 'Yellow banner Done', null, 200);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model|object|\Illuminate\Database\Eloquent\Builder  $room
     * @param  Request  $request
     */
    public function changeBackground(Room $room, int $owner_id, string $image = ''): string|false
    {
        $data = [
            'messageContent' => [
                'message' => 'changeBackground',
                'imgbackground' => $image ?: '',
                'roomIntro' => $room->room_intro ?: '',
                'roomImg' => $room->room_cover ?: '',
                'room_type' => @$room->myType->name ?: '',
                'room_name' => @$room->room_name ?: '',
            ],
        ];
        $json = json_encode($data);

        //        Common::sendToZego('SendCustomCommand', $room->id, $owner_id, $json);
        return $json;
    }

    public function disable_writing(Request $request, $room_id)
    {
        $user_id = Auth::id();
        try {
            $room = $this->roomService->disableWriting($room_id);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }
        $ms = [
            'messageContent' => [
                'message' => 'banRoomWriting',
                'userId' => $user_id,
                'writing_disabled' => $room->writing_disabled,
            ],
        ];

        Common::sendToZego('SendCustomCommand', $room->id, $room->uid, json_encode($ms));

        return Common::apiResponse(
            1,
            'writing permission is changed',
            [
                'room_id' => $room->id,
                'writing_disabled' => $room->writing_disabled,
            ],
            200
        );
    }

    public function changeRoomImage(Request $request)
    {
        $ownerId = $request->owner_id;

        try {
            $room = $this->roomService->changeRoomImage($ownerId);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }

        $json = $this->changeBackground($room, $request->owner_id, PK_IMAGE);

        Common::sendToZego('SendCustomCommand', $room->id, $request->user()->id, $json);

        return Common::apiResponse(true, __('success process'));
    }

    public function adminOwner(Request $request)
    {
        $user = $request->user();
        if (! $request->type || ! $request->room_id) {
            return Common::apiResponse(0, 'missing param', null, 422);
        }

        try {
            $check = $this->roomService->adminOwner($request, $user);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }

        return Common::apiResponse(true, __('success process'), $check, 200);
    }

    public function gameRoom()
    {
        request()->default_background = $this->backgroundRepo->getDefaultImage();
        $game_id = request('game_id');
        $rooms = $this->roomService->getRoomsForGame($game_id);

        return Common::apiResponse(true, '', RoomResource::collection($rooms), 200);
    }

    public function userRooms()
    {
        $user = \Auth::user();
        $room = $this->roomService->userRooms($user->id);
        if ($room !== null) {
            $room = new RoomResource($room);
        }

        return Common::apiResponse(true, '', $room, 200);
    }

    public function commentStatus($roomId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        $result = $this->roomService->commentStatus($roomId, $request);

        $message = ($result === 1) ? 'comment_closed' : 'comment_opened';

        return Common::apiResponse(true, "messages.$message", [], 200);
    }

    public function roomUserDetails($id)
    {
        try {
            $room = $this->roomService->roomDetails($id);

            return Common::apiResponse(true, 'done', new RoomDetailsResource($room));
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }
    }

    public function roomGifts($id)
    {

        $perPage = request('per_page', 10);

        $result = Room::where('uid', $id)->first()?->gifts()->paginate($perPage);

        return Common::apiResponse(true, 'done', GiftRoomResource::collection($result));
    }

    public function check_room(Request $request)
    {

        $request['show'] = true;
        $id = $request->room_id;
        $room = $this->roomService->findRoom($id);
        if (! $room) {
            return Common::apiResponse(0, 'Room not found', null, 404);
        }

        $data = [
            'is_live' => $room->is_live ? true : false,
            'is_locked' => empty($room->room_pass) ? false : true,

        ];

        return Common::apiResponse(true, '', $data, 200);
    }

    protected function blackList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|integer|exists:rooms,id',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $userId = $request->user()->id;
            $room = Room::findOrFail($request->room_id);
            if ($room->uid !== $userId) {
                return Common::apiResponse(0, 'you do not have permission', 400);
            }
            $ids = explode(',', $room->room_black);
            $data = UserResource::collection(User::query()->whereIn('id', $ids)->get());

            return Common::apiResponse(true, '', $data, 200);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }
    }

    protected function addBlock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|integer|exists:rooms,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $userId = $request->user()->id;
            $room = Room::findOrFail($request->room_id);
            $userToBlock = $request->user_id;
            $roomVisitors = $room->roomVisitors->pluck('user_id')->toArray();
            if (! in_array($userToBlock, $roomVisitors)) {
                return Common::apiResponse(0, 'This user is not in this room', null, 404);
            }

            // Check if the current user is the owner of the room
            if ($room->uid !== $userId) {
                return Common::apiResponse(0, 'You do not have permission to perform this action', 400);
            }

            // Get the current blacklist
            $blacklist = $room->room_black ? explode(',', $room->room_black) : [];

            // Check if the user is already in the blacklist
            foreach ($blacklist as $entry) {
                $parts = explode('#', $entry);
                $id = $parts[0] ?? null;

                if ($id === $userToBlock) {
                    return Common::apiResponse(0, 'This user is already in the blacklist', 400);
                }
            }

            // Add the user to the blacklist
            $blacklist[] = $userToBlock.'#'.time(); // Add a timestamp or additional info if needed
            $room->room_black = implode(',', $blacklist);
            $room->save();

            return Common::apiResponse(true, 'User added to the blacklist', 200);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }
    }

    protected function removeBlock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|integer|exists:rooms,id',
            'user_id' => 'required|integer|exists:users,id',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $userId = $request->user()->id;
            $room = Room::findOrFail($request->room_id);
            if ($room->uid !== $userId) {
                return Common::apiResponse(0, 'you do not have permission', 400);
            }
            $ids = explode(',', $room->room_black);
            $updatedIds = [];
            $userToRemove = $request->user_id;

            foreach ($ids as $entry) {
                $parts = explode('#', $entry);
                $id = $parts[0] ?? null;

                if ($id !== $userToRemove) {
                    return Common::apiResponse(0, 'this user not in black list', 400);
                }
            }

            $room->room_black = implode(',', $updatedIds);
            $room->save();

            return Common::apiResponse(true, 'block removed', 200);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }
    }

    private function getBoxes($roomId, $userId)
    {
        if (! PackageHelper::isInstalled('luckyBox')) {
            return collect();
        }

        return BoxUse::query()
            ->with('user', fn ($q) => $q->with('profile')->withoutAppends()->select(['id', 'name', 'uuid']))
            ->where('room_id', $roomId)
            ->where('not_used_num', '>', 0)
            //            ->where('unused_coins', '>', 0)
            ->where('end_at', '>=', now()->timestamp)
            ->whereDoesntHave('picks', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('is_closed', 0)
            ->get();
    }

    private function achievementLevels(int $owner_id)
    {
        return app(UserAchievementContract::class)->roomAchievement($owner_id);
    }

    private function roomCharisma(int $room_id)
    {
        return app(UserCharismaServiceContract::class)->roomCharisma($room_id);
    }

    // on the mic
    private function enterTheRoomCreateOrUpdate($user_id, $owner_id, $room_id)
    {
        EnteredRoom::query()->updateOrCreate([
            'uid' => $user_id,
            'ruid' => $owner_id,
            'rid' => $room_id,
        ], [
            'entered_at' => now(),
        ]);
    }

    private function updateMicrophone2($room_uid, $user_id)
    {
        $user = User::query()->find($user_id);
        if (! $user) {
            return;
        }
        $result = Common::go_microphone_hand_2($room_uid, $user_id);

        $room = Room::query()->where('uid', $room_uid)->first();

        if (! $room) {
            return;
        }
        if ($result) {

            app(UserCharismaServiceContract::class)->RemoveUserRoomWhenLeaveMic($user_id, $room->id);
        }
    }
}
