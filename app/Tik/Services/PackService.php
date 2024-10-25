<?php

namespace App\Tik\Services;

use Exception;

use App\Models\User;
use App\Helpers\Common;
use App\Tik\Repositories\PackRepository;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\WareRepository;
use App\Tik\Repositories\UserVipRepository;
use Illuminate\Database\Eloquent\Collection;
use phpDocumentor\Reflection\Types\Mixed_;


class PackService
{
    public function __construct(
        private readonly PackRepository $packRepository,
        private readonly WareRepository $wareRepository,
        private readonly RoomRepository $roomRepository,
        private readonly UserVipRepository $userVipRepository,
        private readonly UserRepository $userRepository,
    ) {}


    public function changePackMode($type, $privilegeArr, User $user, $isAvailable)
    {
        if (key_exists($type, $privilegeArr)) {
            $privilegeId = $privilegeArr[$type];
            $isWare = $this->wareRepository->checkWare($privilegeId);
            if ($isAvailable && !$isWare) {
                throw new Exception('not found');
            } else if (!$this->packRepository->checkPack($user->id, $privilegeId, $isAvailable)) {

                throw new Exception('not allowed');
            }
            $this->packRepository->changeAvailabilityAllPack($user->id, $privilegeId, $isAvailable);
            switch ($type) {
                case 'country':
                    /*if ($isAvailable) {
                        $user->country_id = null;
                        $user->save();
                    }*/
                    break;
                case 'room':
                    $this->roomRepository->updateRoomStatus($user->id, $isAvailable);
                    break;
            }
        }
    }


    public function userPack($request) : mixed
    {
        $this->packRepository->deleteExpirePack();
        $userId = $request->user_id ?:  $request->user()->id;
        $this->unlock_dress($userId);
        $type = $request->type;
        if (!in_array($type, [1, 2, 3, 4, 5, 6, 7, 25, 22])) throw new \Exception('type not found');
        if ($type == 2) {
            $data = $this->packRepository->packsJoinWithGift($userId, $type);
        } elseif ($type == 22) {
            $this->userVipRepository->deleteExpireUserVip();
            $data = $this->userVipRepository->getByUserId($userId);
        } else {
            $data = $this->packRepository->packsJoinWithWare($userId, $type);
        }
        return  $data;
    }

    public function unlock_dress($userId)
    {
        $vip = Common::getLevel($userId, 3);
        $types = [4, 5, 6, 7, 8];
        $ids = $this->packRepository->getTargetIdsByUserAndType($userId, $types);

        $wares = $this->wareRepository->getWaresByConditions($vip, $types, $ids);

        if ($wares->isEmpty()) return 0;

        foreach ($wares as $ware) {
            $pack = $this->packRepository->getExistingPack($userId, $ware->type, $ware->id);
            if ($pack) continue;

            $data = [
                'user_id'   => $userId,
                'type'      => $ware->type,
                'target_id' => $ware->id,
                'expire'    => $ware->expire ? time() + ($ware->expire * 86400) : 0,
                'is_read'   => 1,
            ];

            $this->packRepository->create($data);
        }
        return true;
    }

    public function usedPack($user, $itemId)
    {
        $types                      = [4, 5, 6, 7];
        $user_dress_after_i_changed = [
            4 => 1,
            5 => 2,
            6 => 3,
            7 => 4
        ];
        $pack  = $this->packRepository->getByUserId($user->id, $itemId);

        if (!$pack)  throw new \Exception('item not found');

        $this->packRepository->updateIsUsedByType($user->id, $pack->type);
        $this->packRepository->updateIsUsedByPackId($user->id, $itemId);

        if (!in_array($pack->type, $types))  throw new \Exception('unusable item');
        $this->userRepository->updateDress($user, $user_dress_after_i_changed, $pack->type, $pack->target_id);

        return  $data['target_id'] = $pack->target_id;
    }

    public function updateDress($user, $type)
    {
        $this->userRepository->nullDress($user, $type);
        return true;
    }
}
