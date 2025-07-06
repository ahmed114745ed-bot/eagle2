<?php

namespace App\Models;

use App\Helpers\Common;
use App\Helpers\UserCoinLogHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class RequestBackgroundImage extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'request_background_images';

    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if ($model->status === 1) {
                $room = Room::where('uid', $model->owner_room_id)->first();
                if (! empty($room)) {
                    $data = [
                        'messageContent' => [
                            'message' => 'changeBackground',
                            'imgbackground' => $model->img ?: '',
                            'roomIntro' => $room?->room_intro ?: '',
                            'roomImg' => $room?->room_cover ?: '',
                            'room_type' => @$room?->myType->name ?: '',
                            'room_name' => @$room?->room_name ?: '',
                        ],
                    ];
                    $json = json_encode($data);
                    $res = Common::sendToZego('SendCustomCommand', $room?->id, $model->owner_room_id, $json);
                }
            }
            UserCoinLogHelper::log(
                $model->owner_room_id,
                'background',
                'request_background_images',
                $model->price
            );

        });


    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_room_id');
    }
}
