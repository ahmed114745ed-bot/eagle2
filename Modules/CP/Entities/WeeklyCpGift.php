<?php

namespace Modules\CP\Entities;

use App\Models\OVip;
use App\Models\Ware;
use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WeeklyCpGift extends Model
{
    protected $guarded = ['id'];

    public function getCreatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if ($model->type == "ware"){
                $model->target = request('target1', $model->target);
                $ware = Ware::find($model->target);
                if ($ware) {
                    if ($ware->type == 4) {
                        $model->sub_type = 'bubble';
                    } elseif ($ware->type == 5) {
                        $model->sub_type = 'intro';
                    } elseif ($ware->type == 6) {
                        $model->sub_type = 'frame';
                    }
                }
            }elseif ($model->type == "vip"){
                $model->target = request('target2', $model->target);
            }elseif ($model->type == "coins"){
                $model->target = request('target3', $model->target);
            }elseif ($model->type == "achievement"){

                $file       = request('target4', $model->target);

                if ($file instanceof  UploadedFile){
                    $url = Common::upload('events', $file);
                }
                $model->target = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });

        static::updating(function ($model) {
            if ($model->type == "ware"){
                $model->target = request('target1', $model->target);
                $ware = Ware::find($model->target);
                if ($ware) {
                    if ($ware->type == 4) {
                        $model->sub_type = 'bubble';
                    } elseif ($ware->type == 5) {
                        $model->sub_type = 'intro';
                    } elseif ($ware->type == 6) {
                        $model->sub_type = 'frame';
                    }
                }
            }elseif ($model->type == "vip"){
                $model->target = request('target2', $model->target);
            }elseif ($model->type == "coins"){
                $model->target = request('target3', $model->target);
            }elseif ($model->type == "achievement"){
                $file       = request('target4', $model->target);
                if ($file instanceof  UploadedFile){
                    $url = Common::upload( 'events', $file);
                    Storage::delete($model->target);
                }
                $model->target = $url ?? '';

            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });
    }

    public function ware()
    {
        return $this->hasOne(Ware::class,'id','target');
    }

    public function vip()
    {
        return $this->hasOne(OVip::class,'id','target');
    }

    public function getTarget1Attribute()
    {
        return $this->target;
    }
    public function getTarget2Attribute()
    {
        return $this->target;
    }
    public function getTarget3Attribute()
    {
        return $this->target;
    }
    public function getTarget4Attribute()
    {
        return $this->target;
    }
}
