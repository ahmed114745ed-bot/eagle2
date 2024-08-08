<?php

namespace App\Interfaces;

interface RoomJobInterface
{
    public function work($roomJob) : array;
    public function sendToZego($data, int $roomId,int $user_id): string;
    public function prepareDataToZego($data) : array;
    public function getVariables($data) : array;
}
