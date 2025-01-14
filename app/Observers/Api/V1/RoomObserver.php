<?php

namespace App\Observers\Api\V1;

use App\Models\Room;

class RoomObserver
{

    public function created(Room $room)
    {

    }


    public function updated(Room $room)
    {

    }


    public function deleted(Room $room)
    {

    }


    public function restored(Room $room)
    {
        //
    }


    public function forceDeleted(Room $room)
    {
        //
    }


    public function creating(Room $room){
        $room->mode = 3;
        $room->muted_users = '';
    }


    public function updating (Room $room){
        if(!$room->enableSaving) return;

        $this->changeMode ($room);
        $this->resetRoomSession ($room);

    }


    public function saving(Room $room){
        if(!$room->enableSaving) return;
        $this->changeMode ($room);

//        $this->resetRoomSession ($room);
//        $v = $room->room_visitor;
//        $av = explode (',',$v);
//        $room->visitor_count = count ($av);
    }

    public function changeMode(Room &$room){
        if ( $room->isDirty('mode')){
            $mics = explode (',',$room->getAttributeValue('microphone'));

            $count = count($mics);
            if ($room->mode == '1'){//16 seats
                if ($count <= 17){
                    $m = array_merge ($mics, array_fill(0, 17 - $count, '0'));
                    $room->microphone = implode (',',$m);
                }
            }elseif ($room->mode == '2'){//12 seats
                if ($count <= 13){
                    $m = array_merge ($mics,array_fill(0, 13 - $count, '0'));
                    $room->microphone = implode (',',$m);
                }else{
                    $m = array_slice ($mics,0,15);
                    $room->microphone = implode (',',$m);
                }
            }elseif ($room->mode == '3'){//9 seats
                if ($count <= 10){
                    $m = array_merge ($mics,array_fill(0, 10 - $count, '0'));
                    $room->microphone = implode (',',$m);
                }else{
                    $m = array_slice ($mics,0,10);
                    $room->microphone = implode (',',$m);
                }
            }elseif ($room->mode == '4'){//2 seats
                if ($count <= 3){
                    $m = array_merge ($mics,array_fill(0, 3 - $count, '0'));
                    $room->microphone = implode (',',$m);
                }else{
                    $m = array_slice ($mics,0,3);
                    $room->microphone = implode (',',$m);
                }
            }elseif ($room->mode == '5'){//3 seats
                if ($count <= 10){
                    $m = array_merge ($mics,array_fill(0,10 - $count, '0'));
                    $room->microphone = implode (',',$m);
                }else{
                    $m = array_slice ($mics,0,10);
                    $room->microphone = implode (',',$m);
                }
            }elseif ($room->mode == '6'){//21 seats
                if ($count <= 22){
                    $m = array_merge ($mics,array_fill(0, 22 - $count, '0'));
                    $room->microphone = implode (',',$m);
                }else{
                    $m = array_slice ($mics,0,22);
                    $room->microphone = implode (',',$m);
                }
            }
            else{// 8 seats
                if ($count > 10){
                    $m = array_slice ($mics,0,10);
                    $room->microphone = implode (',',$m);
                }
            }
        }

    }

    public function resetRoomSession($room){
        $owner_in = $room->is_afk;
        if (!$room->room_visitor && $owner_in != 1){

            $room->room_speak = null;
        }

    }
}
