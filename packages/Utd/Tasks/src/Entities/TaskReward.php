<?php

namespace Utd\Tasks\Entities;

use App\Models\Ware;
use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Utd\Vip\Entities\OVip;

class TaskReward extends Model
{
    use HasFactory, TimestampsWithTimezone;

    public function vip()
    {
        return PackageHelper::checkRelation($this, 'vip', 'belongsTo') ??
            $this->belongsTo(OVip::class, 'target');
    }

    public function ware()
    {
        return $this->belongsTo(Ware::class, 'target');
    }

    protected static function boot()
    {
        parent::boot();
        self::saving(function ($model) {
            if ($model->coins) {
                unset($model->coins);
            }
            if ($model->achievement) {
                unset($model->achievement);
            }
        });
    }
}
