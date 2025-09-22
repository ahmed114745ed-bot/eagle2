<?php

namespace Modules\RoomCup\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\RoomBoom\Entities\TotalRoomGift;

class RoomCupReward extends Model
{
    protected $table = 'room_owner_rewards';

    protected $fillable = [
        'room_id',
        'user_id',
        'total_room_gift_id',
        'amount',
        'type',
    ];

    public function gift(): BelongsTo
    {
        return $this->belongsTo(TotalRoomGift::class, 'total_room_gift_id');
    }
}
