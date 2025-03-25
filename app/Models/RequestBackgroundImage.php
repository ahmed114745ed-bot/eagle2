<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Room;
use App\Helpers\Common;
use Carbon\Carbon;

class RequestBackgroundImage extends Model
{
    protected $table = 'request_background_images';

    protected $guarded = ['id'];

    public function getCreatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    public function owner()
    {
        return $this->belongsTo(User::class,'owner_room_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function($model)
        {
            if($model->status == 1){
                $room = Room::where('uid',$model->owner_room_id)->first();
                if(!empty($room)){
                    $data = [
                        "messageContent"=>[
                            "message"=>"changeBackground",
                            "imgbackground"=>$model->img?:"",
                            "roomIntro"=>$room?->room_intro?:"",
                            "roomImg"=>$room?->room_cover?:"",
                            "room_type"=>@$room?->myType->name?:"",
                            "room_name"=>@$room?->room_name?:""
                        ]
                    ];
                    $json = json_encode ($data);
                    $res = Common::sendToZego ('SendCustomCommand',$room?->id,$model->owner_room_id,$json);
                }
            }
        });
    }
}
