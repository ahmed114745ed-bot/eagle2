<?php

namespace Utd\Room\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TotalRoomGift extends Model
{
    protected $fillable = ['room_id', 'current_total', 'number_of_visitors'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function roomBooms(): HasMany
    {
        return $this->hasMany(\Modules\RoomBoom\Entities\RoomBoom::class, 'total_room_gift_id');
    }

    public function ownerRewards(): HasMany
    {
        return $this->hasMany(\Modules\RoomCup\Entities\RoomCupReward::class, 'total_room_gift_id');
    }
}
