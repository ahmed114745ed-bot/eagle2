<?php

namespace Modules\Moment\Transformers;

use Carbon\Carbon;
use App\Models\Room;
use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\MangerTypeResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {

        $level = Common::level_center(@$this->id);
        if (gettype($level) == 'array') {
            $receiver_level = $level['receiver_level'] ?? 0;
            $sender_level   = $level['sender_level'] ?? 0;
            $receiver_img   = $level['receiver_img'] ?? '';
            $sender_img     = $level['sender_img'] ?? '';
        } else {
            $receiver_level = 0;
            $sender_level   = 0;
            $receiver_img   = '';
            $sender_img     = '';
        }

        $vip       = @Common::ovip_center($this->id) ?? 0;
        $vip_level = (gettype($vip) == 'array') ? $vip['level'] : 0;
        // $frame  = Common::getUserDress($this->id, $this->dress_1, 4, 'img2') ?: Common::getUserDress($this->id, $this->dress_1, 4, 'img1');
        $frameDress  = $this->dress1;
        $frame  = ($this->packs->where('type', 4)->first() != null) ? (($frameDress != null) ? $frameDress->img2 : '') : '';

        $pass_status = false;
        $now_room    = Room::query()->where('uid', $this->now_room_uid)->first();
        if ($now_room) {
            if ($now_room->room_pass) {
                $pass_status = true;
            }
        }

        return [
            'id'                 => @$this->id, // both
            'uuid'               => @$this->uuid ?? '', // both
            'name'               => @$this->name ?: '', // both
            'image'              => @$this->profile->avatar ?: '', // both
            'id_image'             => @$this->specialId?->ware?->show_img ?? '',
            'special_id'          =>  @$this->specialId?->ware?->id ?? 0,
            'receiver_level'     => $receiver_level ?? 0, // both
            'sender_level'       => $sender_level, // both
            'receiver_img'       => $receiver_img, // both
            'sender_img'         => $sender_img, // both
            'vip'                => $vip_level, // both
            'has_color_name'     => Common::hasInPack($this->id, 18), // both
            'frame_id'           => $frame != '' ? @$this->dress_1 : 0,
            'frame'              => $frame,
            'senderLevel'        => $this->total_sender_level,
            'reciverLevel'        => $this->total_received_level,
            'now_room'             => [
                'is_in_room'      => @$this->now_room_uid != 0,
                'uid'             => @(int)$this->now_room_uid,
                'is_mine'         => @$this->id == $this->now_room_uid,
                'password_status' => $pass_status
            ],
            'type_user'            => intval(@$this->type_user) ?: 0, // both
            "manger_type"          => new MangerTypeResource(@$this->mangerType),
            'age'    => Carbon::parse(@$this->profile->birthday)->age,
            'gender' => @$this->profile->gender ?? 1,
            'is_follow'            => $this->is_follow,
            'is_friend'            => $this->isFriends(),
            'image_color'          => @$this->color_image ?? '',
            'special_color'    => @$this->color_id ?? '',
            'color_name'   => common::wareUserVip($this->id, 18, 'color') ?? '',
            'color_name_id'   => common::wareUserVip($this->id, 18, 'id') ?? 0

        ];
    }
}
