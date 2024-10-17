<?php

namespace App\Http\Resources;

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
    public function toArray($request)
    {
        $roomPass = $this->room?->room_pass ?? '';
        $urlEvent = GeneralRole::where('type',$this->event_type)->first();
        return [
            'id'         => $this->id,
            'img'        => $this->img ?:'',
            'type'   => $this->type ?? '',  
            'url'        => ($this->type == 'link' || $this->event_type == 'event')? ($this->url ?? '') :( ($this->event_type == 'pk_event'||$this->event_type == 'weekly_star' ||$this->event_type == 'charge_event' ||$this->event_type =='event_period')? ($urlEvent->url ?? ''):''),
            'isLocked'   =>   $roomPass != '' || $roomPass != null,
            'owner_id'   =>  $this->owner_id ?? 0,
        ];
    }
}
