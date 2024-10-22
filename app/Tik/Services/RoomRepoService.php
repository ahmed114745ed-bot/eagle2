<?php

namespace App\Tik\Services;

use App\Models\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Models\AllGame;
use App\Facades\UserHandling;
use GuzzleHttp\Promise\Utils;
use App\Http\Services\RoomService;
use App\Traits\MultiQueryPagination;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\GiftLogRepository;
use Modules\Charizma\Http\Services\UserCharismaService;
use App\Tik\Repositories\RequestBackgroundImageRepository;



class RoomRepoService
{
    use MultiQueryPagination;

    /**
     * @param Model $model
     */
    public function __construct(
        private readonly RoomRepository $repository,
        private readonly UserRepository $userRepository,
        private readonly GiftLogRepository $giftLogRepository,
        private readonly RequestBackgroundImageRepository $requestBackgroundImageRepository,
    ) {}

    public function getAllRooms($request)
    {
        request()->default_background = \DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->limit(1)->first()->img;
        return $this->repository->all($request);
    }

    public function create($request, $userId)
    {
        $data = array_merge($request->all(), ['uid' => $userId]);
        $room = $this->repository->create($data);
        if ($request->hasFile('room_cover')) {
            $room->room_cover = Common::upload('rooms', $request->file('room_cover'));
            $this->repository->updateRoomUser($room);
        }
        return $room;
    }

    public function findRoomUser($userId)
    {
        return $this->repository->findRoomUser($userId);
    }

    public function createPrivetMessage($fromUserId, $toUserId, $message, $price)
    {
        return $this->repository->createPrivetMessage($fromUserId, $toUserId, $message, $price);
    }



    public function privateComment($toUserId, $message, $ownerId, $fromUser)
    {
        $toUser = $this->userRepository->findById($toUserId);

        $price = Common::getConfig('private_comment_price') ?? 100;

        $room = $this->findRoomUser($ownerId);
        if (!$room)  throw new \Exception(__('room not founded'));

        //validate if user have coins enough or not
        if ($fromUser->di < $price) throw new \Exception(__('not enough coins'));

        $this->createPrivetMessage($fromUser->id, $toUserId, $message, $price);
        $fromUser->di -= $price;
        $fromUser->save();

        return [$toUser, $price];
    }
    public function findRoom($id)
    {
        return $room = $this->repository->findRoom($id);
    }

    public function disableWriting($roomId)
    {
        $room = $this->findRoom($roomId);
        if (!$room) {
            throw new \Exception(__('api_responses.room_not_found'));
        }

        $room->writing_disabled = !$room->writing_disabled;
        $this->repository->updateRoomUser($room);
        return $room;
    }

    public function changeRoomImage($ownerId)
    {
        $room =    $this->findRoomUser($ownerId);

        if (!$room) throw new \Exception(__('room not found'));
        $room->enableSaving = false;
        $room->is_pk_custom = true;
        $this->repository->updateRoomUser($room);
        return $room;
    }

    public function getFirstRoomOwner($ownerId)
    {
        return $this->giftLogRepository->getFirstRoomByOwnerId($ownerId);
    }

    public function roomAdmins($ownerId)
    {
        $room = $this->findRoomUser($ownerId);
        if (!$room) throw new \Exception(__('room not found'));
        $room_admin = explode(',', $room->room_admin);
        return $this->userRepository->getUsers($room_admin);
    }

    public function changePasswordRoom($ownerId)
    {
        $room =  $this->findRoomUser($ownerId);
        if ($room) {
            $room->room_pass = '';
            $this->repository->updateRoomUser($room);
            return  $room;
        }
        return  $room;
    }

    public function quiteRoom($ownerId, User $user)
    {
        $room  = $this->findRoomUser($ownerId);

        $isToZegoCharisma = false;
        //reset user charisma
        if (isset($room->charizma_status)) {
            $userCharismaService = new UserCharismaService();
            $userCharismaService->resetUserCharisma($user->id, $room->id);
            $userDataWithCharisma = $userCharismaService->addTotalEarnedCoinsInUserRoom($room, [$user->id]);
            $isToZegoCharisma = true;
        }
        $res                = Common::quit_hand($ownerId, $user->id);
        $visitorIdsList   = explode(',', $res);

        $user->now_room_uid = 0;
        $user->save();

        /* if ($room->count_room_socket > 0) {
            $room->count_room_socket -= 1;
        } else {
            $room->count_room_socket = 0;
        }*/

        $microphones = explode(',', $room->microphone);
        if (in_array($user->id, $microphones)) {
            UserHandling::calcTime($user->id);
        }


        if ($room->is_afk == null && $room->room_admin == null) {
            $room->is_afk = 0;
        }

        if ($user->id == $ownerId && $room->room_admin == null) {
            $room->is_afk = 0;
        }
        $this->repository->updateRoomUser($room);

        return [$visitorIdsList, $isToZegoCharisma, $userDataWithCharisma, $room->id];
    }


    public function roomUsers($request,)
    {
        $room = $this->findRoomUser($request->owner_id);
        if (!$room) throw new \Exception('Room not found');
        $currentPage = $request->page ?? 1;
        $visitors = null;

        if ($request->has('users') && $currentPage == 1) {
            $room->enableSaving      = false;
            $visitors      = $request->users ?? '';
        }

        $roomAdmin   = $room->room_admin ?? '';
        $roomVisitor = $visitors ?? $room->room_visitor;
        $roomVisitor = explode(',', $roomVisitor);

        $roomAdmin        = explode(',', $roomAdmin);
        $roomAdminActive  = array_intersect($roomVisitor, $roomAdmin);
        $roomVisitorArray = array_diff($roomVisitor, array_merge($roomAdminActive, [$room->uid . '']));
        $users = $this->userRepository->usersRoom($roomAdminActive);

        $usersCount        = count($roomVisitor);
        $countInterested   = $users->count(['users.id']);
        $perPage           = 10;
        //        $diffCountWithPage = $countInterested - ($perPage * $currentPage);

        $users = $users->paginate($perPage);
        if ($currentPage == 1 && in_array($room->uid, $roomVisitor)) {
            $allData[] = $room->owner;
            $allData   = array_merge($allData, $users->items());
        } else {
            $allData = $users->items();
        }

        $allData = collect($allData);
        $diffCountWithPage = $this->getDiffCountWithPage($countInterested, $perPage, $currentPage);

        if ($diffCountWithPage < 0) {
            [$limit, $offset] = $this->getNewLimitAndOffset($countInterested, $perPage, $currentPage);

            $anotherData =  $this->userRepository->anotherUserRoom($roomVisitorArray, $limit, $offset);

            $allData = $allData->merge($anotherData);
        }
        return [$allData, $roomAdminActive];
    }


    public function getRoomsForGame($gameId)
    {
        return $this->repository->getRoomsByGameId(gameId: $gameId, with: ['game', 'boxUse' => fn($q) => $q->where('not_used_num', '>=', 1), 'backgroundImage']);
    }

    public function changeMode($request, $currentMode)
    {
        $room =  $this->findRoomUser($request->owner_id);
        if (!$room) return Common::apiResponse(0, 'not found', null, 404);
        //get last mode of rooms to if is cinema mode and change it update room background
        $lastMode = $room->mode;
        $room->mode = $currentMode;
        $room->save();
        $jsons = [];
        $map = [];
        if ($currentMode == '1') {
            $mode = 'party';
        } elseif ($currentMode == '2') {
            $mode = 'seats12';
        } elseif ($currentMode == '3') {
            $mode = 'cinema';
            $json = $this->changeBackground($room, $request->owner_id, 'custom_image/back-black.png');
            $jsons[] = $json;
        } elseif ($currentMode == '4') {
            $mode = 'game';
            if (!$request->game_id) return Common::apiResponse(0, 'please send game_id', null, 404);
            $game = AllGame::find($request->game_id);
            if (!$game) return Common::apiResponse(false, 'this game does not exists');

            $room->game_id = $request->game_id;
            $room->save();
            $map['game_url'] = $game->mini_url;
        } else {
            $mode = 'topCenter';
        }
        $ms   = [
            'messageContent' => array_merge($map, ['message' => 'roomMode', 'mode' => $mode])
        ];
        $json = json_encode($ms);
        $jsons[] = $json;
        //        Common::sendToZego('SendCustomCommand', $room->id, $request->user()->id, $json);

        if ($lastMode == '3' && $currentMode != '3') {
            $jsons[] = $this->changeBackground($room, $request->owner_id, (new RoomService())->getRoomBackground($room));
        }
        $promises = Common::sendToZego3('SendCustomCommand', $room->id, $request->user()->id, $jsons);

        try {
            Utils::unwrap($promises);
        } catch (\Throwable $e) {
        }
        return Common::apiResponse(1, 'done', null, 201);
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
        //        Common::sendToZego('SendCustomCommand', $room->id, $owner_id, $json);
        return $json;
    }

    public function userRooms($userId)
    {
        return  $this->repository->roomUsers($userId);
    }
}
