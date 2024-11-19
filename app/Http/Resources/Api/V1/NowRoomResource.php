<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NowRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'is_in_room'      => $this->now_room_uid != 0,
            'uid'             => (int) $this->now_room_uid,
            'is_mine'         => $this->id == $this->now_room_uid,
            'password_status' => $this->password_status,
            "id"              => $this->id,
            "room_name"       => $this->room_name,
            "room_cover"      => $this->room_cover,
            "room_background" => $this->final_room_image,
            "mode"            => $this->mode,
            'giftPrice'       => $this->session_string,
        ];
    }
}
