<?php

namespace Utd\RoomCup\Entities;

use App\Support\PackageHelper;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        if (PackageHelper::isInstalled('room')) {
            $totalRoomGiftClass = \Utd\Room\Entities\TotalRoomGift::class;
            return $this->belongsTo($totalRoomGiftClass, 'total_room_gift_id');
        }
        return $this->belongsTo(self::class, 'id'); // Null relation fallback
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room(): BelongsTo
    {
        if (PackageHelper::isInstalled('room')) {
            $roomClass = \Utd\Room\Entities\Room::class;
            return $this->belongsTo($roomClass, 'room_id');
        }
        return $this->belongsTo(self::class, 'id'); // Null relation fallback
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(RoomCupTarget::class, 'target_id');
    }
}
