<?php

namespace Modules\CP\Entities;

use App\Models\OVip;
use App\Models\Vip;
use App\Models\Ware;
use Illuminate\Database\Eloquent\Model;

class CpLevelGift extends Model
{
    protected $guarded = ['id'];

    public function cp_level()
    {
        return $this->belongsTo(CpLevel::class,'vip_id');   
    }

    public function vip()
    {
        return $this->belongsTo(OVip::class,'item_id');   
    }
    public function ware()
    {
        return $this->belongsTo(Ware::class,'item_id');   
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->coins) {
                unset($model->coins);
            }
            if ($model->achievement) {
                unset($model->achievement);
            }
        });
       
    }
}
