<?php

namespace Modules\Events\Entities;

use App\Helpers\Common;
use App\Models\OVip;
use App\Models\Ware;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Reward extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['target1', 'target2', 'target3','target4'];


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
    public function getAppends2(): array
    {
        return $this->getAppends();
    }

    public function weeklyEvent()
    {
        return $this->belongsTo(WeeklyStar::class, 'weekly_star_id');
    }

    public function winners()
    {
        return $this->belongsToMany(Winner::class,'winner_rewards','winner_id');
    }

    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if ($model->type == "ware"){
                $model->target = request('target1', $model->target);
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
//                $model->target = request('target4', $model->target);

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
