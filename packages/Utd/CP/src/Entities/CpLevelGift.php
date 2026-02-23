<?php

namespace Utd\CP\Entities;

use App\Models\Ware;
use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Utd\Vip\Entities\OVip;

class CpLevelGift extends Model
{
    use TimestampsWithTimezone;

    protected $guarded = [];

    protected $appends = ['type_ware'];

    public function cp_level()
    {
        return $this->belongsTo(CpLevel::class, 'vip_id');
    }

    public function vip()
    {
        return PackageHelper::checkRelation($this, 'vip', 'belongsTo') ??
            $this->belongsTo(OVip::class, 'item_id');
    }

    public function ware()
    {
        return $this->belongsTo(Ware::class, 'item_id');
    }

    public function getTypeWareAttribute()
    {
        return $this->ware?->type ?? null;
    }

    public function ware_item()
    {
        return $this->belongsTo(Ware::class, 'ware_item_id');
    }

    public function vip_item()
    {
        return PackageHelper::checkRelation($this, 'vip', 'belongsTo') ??
            $this->belongsTo(OVip::class, 'vip_item_id');
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
            if ($model->type_ware) {
                unset($model->type_ware);
            }
            if ($model->ware_item_id) {
                unset($model->ware_item_id);
            }
            if ($model->vip_item_id) {
                unset($model->vip_item_id);
            }
            unset($model->attributes['type_ware']);
            unset($model->attributes['ware_item_id']);
            unset($model->attributes['vip_item_id']);

        });
    }
}
