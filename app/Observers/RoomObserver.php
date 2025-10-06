<?php

namespace App\Observers;

use App\Models\Room;
use Illuminate\Support\Facades\Log;

class RoomObserver
{
    public function creating(Room $room)
    {
        if($room  == 'audio'){
            $room->mode = 3;
        }
        $room->muted_users = '';
    }

    public function updating(Room $room)
    {
        if (!$room->enableSaving) return;

        if($room->type  == 'audio'){
            $this->changeMode($room);
        }
        $this->resetRoomSession($room);
    }

    public function saving(Room $room)
    {
        if (!$room->enableSaving) return;
        
        if($room->type  == 'audio'){
            $this->changeMode($room);
        }
        //        $this->resetRoomSession ($room);
        //        $v = $room->room_visitor;
        //        $av = explode (',',$v);
        //        $room->visitor_count = count ($av);
    }
    public function changeMode(Room &$room)
    {
        if ($room->isDirty('mode')) {
            $mics = explode(',', $room->all_microphone);
            $count = count($mics);
            Log::info("ChangeMode started", [
                'room_id' => $room->id,
                'old_mode' => $room->getOriginal('mode'),
                'new_mode' => $room->mode,
                'all_microphone' => $room->all_microphone,
                'count_before' => $count
            ]);
           
            if ($room->mode == '0') {
                if ($count <= 10) {
                    $m = array_merge($mics, array_fill(0, 10 - $count, '0'));
                    $room->microphone = implode(',', $m);
                } else {
                    $m = array_slice($mics, 0, 10);
                    $room->microphone = implode(',', $m);
                }
                Log::info("Adding seats", ['added' => $m  ]);

            } elseif ($room->mode == '1') { //16 seats
                if ($count <= 17) {
                    $m = array_merge($mics, array_fill(0, 17 - $count, '0'));
                    $room->microphone = implode(',', $m);
                }
            } elseif ($room->mode == '2') { //12 seats
                if ($count <= 13) {
                    $m = array_merge($mics, array_fill(0, 13 - $count, '0'));
                    $room->microphone = implode(',', $m);
                } else {
                    $m = array_slice($mics, 0, 15);
                    $room->microphone = implode(',', $m);
                }
                Log::info("Adding seats", ['added' => $m  ]);

            } elseif ($room->mode == '3') { //9 seats
                if ($count <= 10) {
                    $m = array_merge($mics, array_fill(0, 10 - $count, '0'));
                    $room->microphone = implode(',', $m);
                } else {
                    $m = array_slice($mics, 0, 10);
                    $room->microphone = implode(',', $m);
                }
            } elseif ($room->mode == '4') { //4 seats
                if ($count <= 3) {
                    $m = array_merge($mics, array_fill(0, 4 - $count, '0'));
                    $room->microphone = implode(',', $m);
                } else {
                    $m = array_slice($mics, 0, 4);
                    $room->microphone = implode(',', $m);
                }
            } elseif ($room->mode == '5') { //3 seats
                if ($count <= 10) {
                    $m = array_merge($mics, array_fill(0, 10 - $count, '0'));
                    $room->microphone = implode(',', $m);
                } else {
                    $m = array_slice($mics, 0, 10);
                    $room->microphone = implode(',', $m);
                }
            } elseif ($room->mode == '6') { //21 seats
                if ($count <= 22) {
                    $m = array_merge($mics, array_fill(0, 22 - $count, '0'));
                    $room->microphone = implode(',', $m);
                } else {
                    $m = array_slice($mics, 0, 22);
                    $room->microphone = implode(',', $m);
                }
            } elseif ($room->mode == '8') { //8 seats
                if ($count <= 9) {
                    $m = array_merge($mics, array_fill(0, 9 - $count, '0'));
                    $room->microphone = implode(',', $m);
                } else {
                    $m = array_slice($mics, 0, 10);
                    $room->microphone = implode(',', $m);
                }
            } else { // 8 seats
                if ($count > 10) {
                    $m = array_slice($mics, 0, 10);
                    $room->microphone = implode(',', $m);
                }
            }
        }
    }

    public function resetRoomSession($room)
    {
        $owner_in = $room->is_afk;
        if (!$room->room_visitor && $owner_in != 1) {

            $room->room_speak = null;
        }
    }
}
