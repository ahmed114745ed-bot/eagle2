<?php

namespace App\Tik\Services;

use App\Models\User;
use App\Helpers\Common;
use App\Facades\UserHandling;
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
    ) {
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
}
