<?php

namespace Modules\RoomBoom\Entities;

use Illuminate\Database\Eloquent\Model;

class TotalRoomGift extends Model
{
    protected $fillable = ['room_id', 'current_total'];
}
