<?php

namespace Modules\CP\Entities;

use App\Helpers\Common;
use App\Helpers\UserCoinLogHelper;
use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Cp extends Model
{
    use TimestampsWithTimezone;

    protected $guarded = ['id'];

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function cpRelation()
    {
        return $this->belongsTo(CpRelation::class, 'cp_relation_id');
    }

    public function relation()
    {
        return $this->belongsTo(CpRelation::class, 'cp_relation_id');
    }

    public function scopeRelation($query)
    {
        return $query->whereHas('relation', function ($query) {
            $query->where('type', 'lovely');
        });
    }

    public function level()
    {
        return $this->belongsTo(CpLevel::class, 'level_id');
    }

    public function scopeRelationType($query, $type)
    {
        return $query->whereHas('relation', function ($query) use ($type) {
            $query->where('type', $type);
        });
    }


    protected static function booted()
    {
        static::created(function ($cp) {
            if (($cp->user_one_id  ?? null) && ($cp->price ?? 0) > 0) {
                $amountBefore =  Common::getCurrentBalance($cp->user_one_id);
                $logAmount = -abs($cp->price);
                UserCoinLogHelper::log(
                    $cp->user_one_id ,
                    'cp',
                    'cps',
                    $logAmount ?? 0,
                    $amountBefore ?? 0,
                    'cp'
                );
                
            }
        });
    }

}
