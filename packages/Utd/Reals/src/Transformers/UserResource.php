<?php

namespace Utd\Reals\Transformers;

use App\Helpers\Common;
use App\Http\Resources\Api\V1\MangerTypeResource;
use App\Support\PackageHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $pass_status = false;
        if (PackageHelper::isInstalled('room')) {
            $roomClass = PackageHelper::getEntity('room');
            $now_room = $roomClass::query()->where('uid', $this->id)->first();
            if ($now_room && $now_room->room_pass) {
                $pass_status = true;
            }
        }

        return [
            'id' => @$this->id,
            'name' => @$this->name ?: '',
            'image' => @$this->profile->avatar ?: '',
            'uuid' => @$this->uuid,
            'id_image' => @$this->specialId?->ware?->show_img ?? '',
            'special_id' => @$this->specialId?->ware?->id ?? 0,
            'is_follow' => @(bool) Common::IsFollow(@$request->user()->id, $this->id),
            'vip_level' => (int) (@$this->UserVip->level ?? 0),
            'sender_level' => (int) (@$this->total_sender_level ?? 0),
            'now_room' => [
                'is_in_room' => @$this->now_room_uid !== 0,
                'uid' => @(int) $this->now_room_uid,
                'is_mine' => @$this->id === $this->now_room_uid,
                'password_status' => $pass_status,
            ],
            'type_user' => (int) (@$this->type_user) ?: 0,
            'manger_type' => new MangerTypeResource(@$this->mangerType),
        ];
    }
}
