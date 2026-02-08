<?php

namespace App\Contracts;

interface RoomRepositoryContract
{
    public function findById($id);
    
    public function findRoomUser($userId, $withoutAppends = true);
    
    public function findRoomUserEnableAudio($userId);
    
    public function findRoom($roomId);
    
    public function getRooms($ids);
    
    public function all($req, $ids = []);
    
    public function updateRoomStatus($userId, $isAvailable);
    
    public function updateMicRoom($room, $mic);
    
    public function findUserRoom($ownerId, $selectRow = "*");
    
    public function findTypeUserRoom($ownerId, $type = 'audio', $selectRow = "*");
    
    public function findUserRoomById($ownerId, $selectRow = "*");
}
