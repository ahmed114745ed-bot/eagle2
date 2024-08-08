<?php

namespace App\Tik\Services;

use Exception;

use App\Models\User;
use App\Tik\Repositories\PackRepository;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\WareRepository;


class PackService
{
    public function __construct(
        private readonly PackRepository $packRepository,
        private readonly WareRepository $wareRepository,
        private readonly RoomRepository $roomRepository
    ) {
    }


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
}
