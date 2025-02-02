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
    public static function boot()
    {
        parent::boot();

        static::creating(function($model)
        {
            if($model->status == 1){
                $room = Room::where('uid',$model->owner_room_id)->first();
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
                $res = Common::sendToZego ('SendCustomCommand',$room->id,$model->owner_room_id,$json);
            }
        });
    }
}
