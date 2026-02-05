<?php

namespace Utd\Gifts\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Utd\Gifts\Support\ModelResolver;

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

    protected static function boot()
    {
        parent::boot();
        
    }

    /**
     */
    public function gift()
    {
        return $this->belongsTo(Gift::class, 'giftId', 'id');
    }

    /**
     */
    public function sender()
    {
        $userModel = ModelResolver::getUserModel();
        
        if (!$userModel) {
            return ModelResolver::emptyRelation($this);
        }
        
        return $this->belongsTo($userModel, 'sender_id');
    }

    /**
     */
    public function receiver()
    {
        $userModel = ModelResolver::getUserModel();
        
        if (!$userModel) {
            return ModelResolver::emptyRelation($this);
        }
        
        return $this->belongsTo($userModel, 'receiver_id');
    }

    public function roomOwner()
    {
        $userModel = ModelResolver::getUserModel();
        
        if (!$userModel) {
            return ModelResolver::emptyRelation($this);
        }
        
        return $this->belongsTo($userModel, 'roomowner_id');
    }

    /**
     */
    public function room()
    {
        // محاولة استخدام PackageHelper إذا كان موجوداً
        $packageHelperRelation = ModelResolver::checkRelation($this, 'room', 'belongsTo');
        if ($packageHelperRelation !== null) {
            return $packageHelperRelation;
        }
        
        $roomModel = ModelResolver::getRoomModel();
        
        if (!$roomModel) {
            return ModelResolver::emptyRelation($this);
        }
        
        return $this->belongsTo($roomModel, 'room_id');
    }

    public function moment(): BelongsTo
    {
        $momentModel = ModelResolver::getMomentModel();
        
        if (!$momentModel) {
            return ModelResolver::emptyRelation($this);
        }
        
        return $this->belongsTo($momentModel, 'moent_id');
    }

    /**
     */
    public function agency()
    {
        $agencyModel = ModelResolver::getAgencyModel();
        
        if (!$agencyModel) {
            $nullAgencyModel = ModelResolver::getNullAgencyModel();
            return $this->belongsTo($nullAgencyModel, 'agency_id', 'id')->whereRaw('1 = 0');
        }
        
        return $this->belongsTo($agencyModel, 'agency_id');
    }
}
