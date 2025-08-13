<?php

namespace Modules\RoomBoom\Entities;

use Illuminate\Database\Eloquent\Model;

class RoomBoom extends Model
{
    protected $fillable = ['total_room_gift_id', 'room_boom_level_id', 'started_at', 'ended_at', 'total_gifts_value'];
}
