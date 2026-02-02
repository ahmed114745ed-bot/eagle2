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
}
