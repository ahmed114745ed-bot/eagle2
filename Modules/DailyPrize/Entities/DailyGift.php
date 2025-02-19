<?php

namespace Modules\DailyPrize\Entities;

use App\Models\OVip;
use App\Models\Ware;
use App\Helpers\Common;
use App\Services\RedisService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DailyGift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
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
    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if ($model->gift_type == "ware"){
                $model->target = request('target1', $model->target);
            }elseif ($model->gift_type == "vip"){
                $model->target = request('target2', $model->target);
            }elseif ($model->gift_type == "coins"){
                $model->target = request('target3', $model->target);
            }elseif ($model->gift_type == "achievement"){

                $file       = request('target4', $model->target);

                if ($file instanceof  UploadedFile){
                    $url = Common::upload(DIRECTORY_SEPARATOR.'events', $file);
                }
                $model->target = $url ?? '';
            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);


        });

        static::updating(function ($model) {
            if ($model->gift_type == "ware"){
                $model->target = request('target1', $model->target);
            }elseif ($model->gift_type == "vip"){
                $model->target = request('target2', $model->target);
            }elseif ($model->gift_type == "coins"){
                $model->target = request('target3', $model->target);
            }elseif ($model->gift_type == "achievement"){
                $file       = request('target4', $model->target);
                if ($file instanceof  UploadedFile){
                    $url = Common::upload(DIRECTORY_SEPARATOR . 'events', $file);
                    $file = str_replace('\\', '/', $model->target);
                    Storage::delete($file);
                }
                $model->target = $url ?? '';
//                $model->target = request('target4', $model->target);

            }
            unset($model->target1);
            unset($model->target2);
            unset($model->target3);
            unset($model->target4);
        });

        static::deleted(function ($model) {
            \App\Facades\RedisService::update('daily-gift-count', DailyGift::count());
        });
        static::created(function ($model) {
            \App\Facades\RedisService::update('daily-gift-count', DailyGift::count());
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
        return $this->gift_type == 'ware' ? $this->target : null;
    }
    public function getTarget2Attribute()
    {
        return $this->gift_type == 'vip' ? $this->target : null;
    }
    public function getTarget3Attribute()
    {
        return $this->gift_type == 'coins' ? $this->target : null;
    }
    public function getTarget4Attribute()
    {
        return $this->gift_type == 'achievement' ? $this->target : null;
    }



}
