<?php

namespace Utd\Gifts\Entities;

use App\Models\Agency;
use App\Models\User;
use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Utd\Moments\Entities\Moment;
use Utd\Room\Entities\Room;

/**
 * GiftLog Model
 */
class GiftLog extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'gift_logs';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'giftId', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function roomOwner()
    {
        return $this->belongsTo(User::class, 'roomowner_id');
    }

    public function room()
    {
        return PackageHelper::checkRelation($this, 'room', 'belongsTo')
            ?? $this->belongsTo(Room::class, 'room_id');
    }

    public function moment(): BelongsTo
    {
        return PackageHelper::checkRelation($this, 'moment', 'belongsTo')
            ?? $this->belongsTo(Moment::class, 'moent_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }
}
