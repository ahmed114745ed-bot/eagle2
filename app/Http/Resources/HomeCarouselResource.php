<?php

namespace App\Http\Resources;

use App\Models\Pk;
use Modules\Events\Entities\GeneralRole;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeCarouselResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    private function getRoomTwoLastPk(int $roomId)
    {
        return Pk::query()
            ->where('room_id', $roomId)
            ->orderByDesc('created_at')
            ->limit(2)
            ->get();
    }

    public function toArray($request)
    {
        $roomPass = $this->room?->room_pass ?? '';
        $ownerRoom = $this->user?->ownerRoom;
        $pks = !is_null($ownerRoom?->id) ? $this->getRoomTwoLastPk($ownerRoom?->id) : null;

        $urlEvent = GeneralRole::where('type',$this->event_type)->first();
        $data =  [
            'id'         => $this->id,
            'img'        => $this->img ?:'',
            'type'   => $this->type ?? '',
            'url'        => ($this->type == 'link' || $this->event_type == 'event')? ($this->url ?? '') :( ($this->event_type == 'pk_event'||$this->event_type == 'weekly_star' ||$this->event_type == 'charge_event' ||$this->event_type =='event_period')? ($urlEvent->url ?? ''):''),
            'isLocked'   =>   $roomPass != '' || $roomPass != null,
            'owner_id'   =>  $this->owner_id ?? 0,

        ];

        if($this->type == 'room'){
            $data += ['room' => [
                "id" => @$ownerRoom->id ?? 0,
                "owner_uuid" => @$this->uuid,
                "room_name" => @$ownerRoom->room_name ?? '',
                "room_cover" => @$ownerRoom->room_cover ?? '',
                "room_background" => @$ownerRoom->final_room_image ?? '',
                "mode" => @$ownerRoom->mode ?? 0,
                'giftPrice' => @$ownerRoom->session_string ?? "0",
                "is_pk"               => (@$pks[0]) && @$pks[0]->end_at >= now() ? @$pks[0]->status : 0,
                "show_pk"             => @$ownerRoom->is_show_pk ?? 0,
                'password_status'     => !(@$ownerRoom->room_pass == ""),
                'type-number'                => @$ownerRoom->room_type ?? 0,
                'type' => @$ownerRoom->myType ?: new \stdClass(),
            ]];
        }

        return $data;
    }
}
