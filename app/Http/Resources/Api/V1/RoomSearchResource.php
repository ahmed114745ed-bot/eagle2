<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Pk;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\Police;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $pks = !is_null(@$this?->room_id) ? $this->getRoomTwoLastPk(@$this?->room_id) : null;
        $room = Room::find(@$this->id);
        return [
            'id' => $this->id ?? 0,
            'room_id' => $this->id ?? 0,
            "room_name" => $this->room_name ?? '',
            "numid" => $this->numid ?? 0,
            "hot" => $this->hot ?? '',
            "room_cover" => $this->room_cover ?? '',
            "room_intro" => $this->room_intro ?? '',
            "room_background" => @$room->final_room_image ?? '',
            "room_welcome" => $this->room_welcome ?? '',
            "mode" => @$this->mode ?? 0,
            'giftPrice' => @$this->session_string ?? "0",
            "show_pk"             => @$this->is_show_pk ?? 0,
            'password_status'     => !(@$this->room_pass == ""),
            'type-number'                => @$this->room_type ?? 0,
            'type' => @$room->myType ?: new \stdClass(),
            "is_pk"               => (@$pks[0]) && @$pks[0]->end_at >= now() ? @$pks[0]->status : 0,

            "room_pass" => $this->room_pass ?? '',
            "uid" => $this->uid ?? 0,
            'owner_id' =>  $this->uid ?? 0,
            'owner_uuid' => $this->uuid ?? '',
            "name" => $this->name ?? '',

            "nickname" => $this->nickname ?? '',


        ];
    }

    private function getRoomTwoLastPk(int $roomId)
    {
        return Pk::query()
            ->where('room_id', $roomId)
            ->orderByDesc('created_at')
            ->limit(2)
            ->get();
    }
}
