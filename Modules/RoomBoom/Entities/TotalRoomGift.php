<?php

namespace Modules\RoomBoom\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TotalRoomGift extends Model
{
    protected $fillable = ['room_id', 'current_total'];

    public function roomBooms(): HasMany
    {
        return $this->hasMany(RoomBoom::class, 'total_room_gift_id');
    }

    public function topContributors(): HasMany
    {
        return $this->hasMany(RoomBoomTopContributor::class, 'total_room_gift_id');
    }
}
