<?php

namespace Utd\RoomCup\Entities;

use App\Models\User;
use App\Support\PackageHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Utd\Room\Entities\Room;
use Utd\Room\Entities\TotalRoomGift;

class RoomCupReward extends Model
{
    protected $table = 'room_cup_rewards';

    protected $fillable = [
        'room_id',
        'user_id',
        'total_room_gift_id',
        'target_id',
        'amount',
        'type',
    ];

    public function gift(): BelongsTo
    {
        return PackageHelper::checkRelation($this, 'room', 'belongsTo') ??
            $this->belongsTo(TotalRoomGift::class, 'total_room_gift_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room(): BelongsTo
    {
        return PackageHelper::checkRelation($this, 'room', 'belongsTo') ??
            $this->belongsTo(Room::class, 'room_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(RoomCupTarget::class, 'target_id');
    }
}
