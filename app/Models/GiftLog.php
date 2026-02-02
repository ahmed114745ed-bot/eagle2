<?php

namespace App\Models;

use App\Traits\HostLevelTrait;
use Modules\CP\Traits\CpGiftLog;
use Modules\Moment\Entities\Moment;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftLog extends Model
{
    use TimestampsWithTimezone, CpGiftLog, HostLevelTrait;

    protected $table = 'gift_logs';

    protected $guarded = [];

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
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function moment(): BelongsTo
    {
        if (! class_exists(Moment::class)){
            return $this->belongsTo(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->belongsTo(Moment::class, 'moent_id');
    }

    public function agency()
    {
        $agencyClass = \App\Helpers\AgencyPackageHelper::getAgencyClass();
        
        if (!$agencyClass) {
            // Return a stub relationship using a safe empty model pattern
            // Using NullAgency class to avoid column mismatch errors
            return $this->belongsTo(\App\Models\NullAgency::class, 'agency_id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->belongsTo($agencyClass, 'agency_id');
    }
}
