<?php

namespace Utd\Room\Entities;

use App\Support\PackageHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Utd\RoomCup\Entities\RoomCupReward;
use Utd\RoomBoom\Entities\RoomBoom;

class TotalRoomGift extends Model
{
    protected $fillable = ['room_id', 'current_total', 'number_of_visitors'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function roomBooms(): HasMany
    {
        return PackageHelper::checkRelation($this, 'roomBoom', 'hasMany') ??
            $this->hasMany(RoomBoom::class, 'total_room_gift_id');
    }

    public function ownerRewards(): HasMany
    {
        return PackageHelper::checkRelation($this, 'roomCup', 'hasMany') ??
            $this->hasMany(RoomCupReward::class, 'total_room_gift_id');
    }
}
